# CampFinder AI — Technical Implementation Strategy

**Version:** 1.0
**Date:** 2026-06-11
**Status:** Reflects the as-built system (post-implementation-plan iterations included)
**Audience:** Engineers evaluating or re-implementing this architecture. Written to be language- and framework-agnostic; a mapping to the concrete reference implementation appears in the appendix.

---

## 1. System Summary

CampFinder is a natural-language planning application: a user describes a multi-constraint scheduling problem in free text ("two kids, ages 6 and 9, art and soccer, Park Slope, ~$400/week, same place if possible"), and the system produces a complete, interactive 10-week plan that the user can then refine without re-stating anything.

The defining architectural decision is the **two-stage hybrid AI design**:

1. **Stage 1 — LLM as parser only.** A single, small, low-temperature LLM call converts free text into a strict structured-criteria object. The model has no knowledge of the inventory and makes no recommendations.
2. **Stage 2 — Deterministic matching engine.** Conventional code queries the inventory, scores every candidate against the parsed criteria, and assembles the plan. This stage is fast (milliseconds), free, repeatable, and incapable of hallucination.

Everything interactive after the first response — selecting between options, locking choices, blocking weeks, re-planning, sharing — runs through Stage 2 alone. The LLM is touched exactly once per user intent.

---

## 2. Architectural Evolution (and Why It Matters)

The system was **not** designed this way initially. The first implementation followed the original FRD: a single tool-using agent (large model, ~15 reasoning steps) given database-query tools (`SearchCamps`, `GetFacilityDetails`, `CheckSiblingOverlap`) and a structured output schema, asked to reason its way to a full multi-child plan.

That design was replaced mid-build. The observed failure modes are general to agentic product features and worth recording:

| Problem with the tool-using agent | Consequence |
|---|---|
| Many sequential tool-call round trips (per child × per week) | 30–60s+ response times; unacceptable for an interactive UI |
| Token cost scales with inventory size surfaced through tools | Expensive per request; cost grows with catalog |
| Non-deterministic plan assembly | Same prompt produces different plans; impossible to test, hard to demo |
| Model can emit entity IDs that don't exist | Requires defensive validation of every referenced record |
| Refinement (swap one week) requires a full re-reasoning pass | Every small UI interaction pays full LLM latency again |

The replacement insight: **the LLM was only adding value at the language boundary.** Understanding "my 9-year-old is into soccer and we're in Park Slope" is a language problem. Ranking 1,500 inventory rows against five numeric/categorical constraints is a database problem. Splitting at that boundary recovered:

- **Latency:** one ~2–3s parse call, then instant computation.
- **Determinism:** identical criteria always yield identical plans — which also unlocks the share/restore feature (§7) for free.
- **Testability:** the matching engine is plain code with no mocked LLM needed.
- **Cost:** one small-model call per new prompt; zero LLM cost for all refinement.
- **Integrity:** every entity in the response comes from a real database row by construction.

**Generalizable rule:** in an LLM-backed feature, push the model to the smallest surface where natural language genuinely is the problem, and keep all retrieval, ranking, and assembly in deterministic code. Use a tool-loop agent only when the search strategy itself must be reasoned about at runtime.

> Note: the original agent and its three tools have been removed from the codebase; they are preserved in git history for hackathon-history reference.

---

## 3. Stage 1 — Structured Parsing

### 3.1 Contract

Input: a free-text prompt (capped at 2,000 chars at the API boundary).
Output: a strict criteria object:

```json
{
  "children": [
    { "name": "string", "age": "integer", "categories": ["string"] }
  ],
  "borough": "string (one of 5 canonical values, or empty)",
  "budget_cents_per_week": "integer",
  "schedule_preference": "full_day | half_day_am | half_day_pm | any",
  "prefer_same_facility": "boolean"
}
```

### 3.2 Implementation choices

- **Small, cheap model** (mini-tier), **temperature 0**, tight max-token and timeout budgets (~1k tokens, 15s). Parsing is an extraction task; creativity is a liability.
- **Provider-enforced structured output** (JSON-schema / "strict mode"), not "please reply in JSON" prompting. The schema is the contract the frontend and matcher rely on.
- **Normalization pushed into the prompt:** the instructions enumerate the closed category vocabulary (10 categories) with example synonyms ("minecraft" → stem, "ninja" → martial_arts), require neighborhoods to be resolved to a canonical borough, and define defaults for every omitted field (no budget → $500/week; no interests → `general`; multiple children → `prefer_same_facility = true`). The goal is that *everything downstream of the parser deals only in canonical enums and integers.*
- **Defense in depth on normalization:** the matching engine keeps its own neighborhood→borough fallback map, because the parser occasionally returns the raw neighborhood despite instructions. Cheap insurance beats retry loops.

### 3.3 Hard-won schema lessons (from the commit history)

Provider strict modes are stricter than the documentation suggests. Two classes of fixes were needed in practice:

1. Every object level in the schema — including nested ones — must explicitly forbid additional properties.
2. "Strict" structured output requires every field to be marked required; optionality must be modeled as a required field with a sentinel/empty value (e.g., `borough: ""`), not an omitted key.

Treat the structured-output schema as code: it will need debugging against the actual provider, not just the spec.

---

## 4. Data Model

Two entities, deliberately denormalized for prototype speed:

- **Facility** — a physical provider location: name, borough, neighborhood, address, lat/long, amenities, lunch default, description.
- **Camp (session)** — one program at one facility for one specific week: category, age range (min/max), week start/end dates, schedule type (full/half-day AM/PM), price, capacity, enrollment count, waitlist count, lunch override.

Derived (computed, never stored): `is_full`, `spots_remaining`, and a three-state `availability_status` (`available` / `almost_full` when ≤3 spots / `waitlist`).

Key modeling decisions:

- **A "camp" is a week-session, not a program.** A program running 10 weeks is 10 rows. This makes the entire matching problem a flat filter+score over rows — no recurrence expansion at query time. It costs row count (~1,500 rows for 27 facilities) and would need revisiting at marketplace scale, but it collapses the hardest part of scheduling logic into `WHERE week_start = ?`.
- **Money in integer minor units** (cents) everywhere — parser output, storage, scoring, API, UI formatting at the last moment.
- **Availability is data, not state machine.** Capacity/enrolled/waitlist are seeded counters; the prototype has no booking flow mutating them.
- **The summer calendar is a fixed 10-element list of canonical week-start dates** shared (currently duplicated — see §9) between backend and frontend. All week alignment is exact-date matching against this list, which avoids timezone/date-range comparison bugs that surfaced early on the embedded database.

### 4.1 Seed data as a first-class deliverable

For a recommendation demo, the dataset *is* the product. The seeders were iterated as much as the code: 27 facilities across all 5 boroughs with real neighborhood coordinates; facility "sizes" (small/medium/large) controlling how many categories and weeks they cover; ~1,500 sessions; a tuned availability distribution (~60% open / 25% almost-full / 15% waitlisted); price bands by schedule type; unique generated camp names per category. A read-only **data explorer page** (`/data`) exists purely so reviewers can verify the inventory the matcher is choosing from — recommended practice for any ranking demo, since "is this a good recommendation?" is unanswerable without visibility into the candidate pool.

---

## 5. Stage 2 — The Matching Engine

The engine is a single stateless service with two phases per request.

### 5.1 Phase A: shortlist building

For **each child × each of the 10 weeks**:

1. **Hard filters** (query level): age within range, exact week, price ≤ budget, schedule type if specified, minus any explicitly excluded session IDs (see §6).
2. **Soft scoring** (in memory) of every surviving candidate:

   | Factor | Points |
   |---|---|
   | Category matches a stated interest | +30 (+10 more if it's the child's *primary* interest) |
   | Category is `general` (no interest match) | +10 |
   | Facility in the requested borough | +20 |
   | Availability: open / almost-full / waitlist | +15 / +8 / +0 |
   | Full-day schedule | +5 |
   | Lunch provided | +3 |

3. **Two-tier sort:** interest-matched candidates always rank above non-matched ones, then by score descending within each tier. This guarantees the UI can render a visual divider between "matches your interests" and "also available" without re-deriving the boundary.
4. **Take the top 5** per cell, plus the best *interest-matched* waitlisted option tracked separately (so a great-but-full camp can be surfaced as a deliberate "join the waitlist" suggestion rather than silently dropped).
5. **Distance** from the user's borough centroid to each facility (Haversine over stored lat/long) is computed and attached for display and tooltips. Borough centroids are an accepted approximation — the prototype never collects a real address.

Notably absent: a borough hard filter. Location is a *score*, not a *gate* — out-of-borough camps remain eligible and simply rank lower. For sparse inventory this is the right default; it degrades to "best available anywhere" instead of empty weeks.

### 5.2 Phase B: plan assembly

The shortlist is folded into the response plan, applying user constraints in priority order per cell (child × week):

1. **Blocked** → emit an empty blocked cell (vacation, travel).
2. **Locked** → emit exactly the locked selection as the only option; its price is committed.
3. **Otherwise** → emit the candidate options (plus the waitlist suggestion if not already present) with `selected_index: 0` as the default choice.

Then a **sibling coordination pass** runs across each week: collect every facility that appears in *all* children's option lists for that week, pick the one whose options are collectively highest-ranked, and flip each child's `selected_index` to that facility's option (adjusting the running total cost). Blocked/locked cells opt out of this pass. The result: the default plan visibly co-locates siblings whenever the ranked options allow it, without ever overriding an explicit user choice.

Human-readable strings (per-option "reason", per-child summary, plan notes) are **template-generated from the scoring facts** — not LLM-generated. They read slightly mechanical but are instant, free, and always consistent with the actual ranking. This is a conscious trade; see §9 for the upgrade path.

---

## 6. The Refinement Protocol (Stateless Constraint Round-Trips)

There is exactly **one API endpoint** (`POST /api/recommend`) and **no server-side session state**. All interactivity is achieved by the client echoing constraints back:

| Request field | Meaning | Effect |
|---|---|---|
| `prompt` | original free text | parsed by the LLM **only if** `parsed_criteria` is absent |
| `parsed_criteria` | previously returned criteria object | **skips Stage 1 entirely** — the critical latency/cost optimization for every interaction after the first |
| `blocked_weeks` | per-child week-start lists | cells emitted empty |
| `locked_camps` | per-child, per-week pinned selection objects | cells pass through verbatim |
| `exclude_camps` | per-child, per-week session-ID lists | hard-filtered out of candidate queries |

The client-side **re-plan** flow composes these: locked weeks are pinned, and for every unlocked week the client sends the IDs of all currently displayed options as exclusions — so "re-plan" is guaranteed to surface *fresh* inventory rather than reshuffling the same top 5. (Each re-plan replaces the previous exclusion set rather than accumulating it; acceptable for a prototype, see §9.)

Local-only interactions — choosing among the returned options, toggling lock/block flags, recomputing totals — never hit the server at all. The client owns selection state; the server owns candidate generation.

Properties of this protocol worth keeping in any reimplementation:

- **Stateless server** → trivially horizontally scalable, no session expiry bugs, refresh-safe.
- **Criteria-as-token:** the parsed criteria object functions as a portable, human-inspectable "session token" that the client (or a URL) can replay.
- **Idempotent regeneration:** same criteria + same constraints = same plan, byte-for-byte. This is only possible because Stage 2 is deterministic.

---

## 7. Sharing by Reconstruction

The share feature stores **no plan data server-side**. The shareable URL encodes (base64, in the URL *fragment*) a compact payload: the original prompt, the parsed criteria, and a per-cell selection map (`selected_index`, locked, blocked). On load, the client detects the fragment, replays the criteria through the standard endpoint (no LLM call), and re-applies the saved selections on top of the regenerated plan.

This works *because of* deterministic regeneration (§6) and fails gracefully when it can't (a missing saved week simply keeps defaults). Using the fragment keeps the payload out of server logs and avoids any persistence layer. The honest limitation: a share link is only stable while the underlying inventory and scoring weights are stable — it's a *recipe*, not a *snapshot*. A production system would persist a plan snapshot with an ID instead.

---

## 8. Frontend Architecture

A single server-rendered page with one reactive client-side component owning all state — no SPA framework, no client routing, no build-step view layer. The component holds: prompt, loading/error flags, the results tree, parsed criteria, constraint maps, and view state (current month, open modals).

Patterns of note, framework-agnostic:

- **The API response shape *is* the view model.** The plan tree (children → weeks → options) renders directly; the client adds only ephemeral fields (`selected_index`, `locked`, `blocked`) that round-trip through the refinement protocol.
- **Month pagination instead of horizontal scroll.** The original swim-lane design (10 columns, sideways scrolling) was replaced post-plan with a June/July/August paginated grid that fits on screen — a fixed mapping of week indices to three month views. Lesson: for fixed-cardinality timelines, pagination beats scroll for demo legibility.
- **Derived presentation is computed, with one deliberate cache.** Totals, subtotals, and badges recompute from state on render. The exception is the shared-facility color map (which facility gets which sibling-highlight color per week) — an O(children × options) computation memoized per week and explicitly invalidated whenever a new plan arrives. If you cache derived view state, pair every cache with an explicit invalidation hook on data replacement.
- **Sibling highlighting is computed client-side** from the plan itself (same facility ID appearing in multiple children's options for a week), distinguishing "same camp" from "same facility", with stable per-facility colors within a week.
- **Failure UX:** generous request timeout, a single friendly error banner, and a retry path that reuses the parsed criteria — so a Stage 2 hiccup never re-bills Stage 1.

---

## 9. Known Prototype Compromises (Productionization Map)

Deliberate shortcuts, each with its production answer:

| Compromise | Production path |
|---|---|
| Scoring weights hardcoded in the engine | Externalize to config; consider per-user weight tuning or learned ranking |
| Summer week list + labels duplicated in backend and frontend | Serve calendar metadata from the API |
| Borough-centroid distance, no real geocoding | Geocode user address; precompute facility distance at query time |
| Single LLM parse, no clarifying dialogue | Add a "did you mean / what's missing" turn when criteria are incomplete (the FRD's deferred follow-up-conversation stretch goal) |
| Templated reason/summary strings | Optional second small-LLM pass to narrate the *already-decided* plan — narration may use a model; selection must not |
| Re-plan exclusions don't accumulate across rounds | Persist exclusion history per session/plan |
| Share links are recipes, not snapshots | Persist plan snapshots server-side with IDs |
| No booking: availability counters are static seed data | Real inventory integration; locking/eventual-consistency around capacity |
| Generic catch-all error handling, no rate limiting, no auth | Standard API hardening; per-IP throttling on the LLM-bearing endpoint is the first priority since Stage 1 spends money |
| Embedded single-file database | Any relational store; the query shapes are trivial (single-table filters + one join) |

The two-stage split itself is *not* a prototype compromise — it is the part of this design most worth carrying into production.

---

## Appendix A — Mapping to the Reference Implementation

The concrete stack is Laravel 12 / PHP, an embedded SQLite database, the Laravel AI SDK with OpenAI (`gpt-4o-mini` for parsing), and an Alpine.js + Tailwind frontend. Component mapping:

| Concept in this document | File |
|---|---|
| Stage 1 parser (LLM, structured output) | `app/Ai/Agents/PromptParser.php` |
| Stage 2 matching engine (shortlist, scoring, assembly, sibling pass) | `app/Services/CampMatcher.php` |
| Single API endpoint / refinement protocol | `app/Http/Controllers/RecommendController.php`, `routes/web.php` |
| Entities and derived availability | `app/Models/Camp.php`, `app/Models/Facility.php` |
| Inventory generation | `database/seeders/FacilitySeeder.php`, `database/seeders/CampSeeder.php` |
| Frontend component (state, refinement, sharing, rendering) | `resources/views/welcome.blade.php` |
| Data explorer | `app/Http/Controllers/DataViewController.php`, `resources/views/data.blade.php` |
| Superseded tool-using agent | removed; see git history (`app/Ai/Agents/CampRecommender.php`, `app/Ai/Tools/*` prior to removal) |

## Appendix B — Document Lineage

- `docs/FRD.md` — original functional requirements (single tool-using agent design, 7 categories, swim-lane UI).
- `docs/IMPLEMENTATION_PLAN.md` — original 7-phase build plan.
- Post-plan deltas captured in this document: two-stage architecture replacing the agent; multi-option grid with select/lock/block/waitlist; re-plan with exclusion; 10 categories (martial arts, equestrian, pets added); 27 facilities / ~1,500 sessions; month pagination replacing horizontal swim lanes; distance display; sibling auto-select and color-coded badges; share links; review-selections panel; register modal (stub); `/data` explorer.
