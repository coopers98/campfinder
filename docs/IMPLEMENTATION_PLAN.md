# CampFinder AI - Implementation Plan

**Date:** 2026-03-13
**Approach:** Sequential phases, each buildable and testable independently.
**Estimated phases:** 7 (each a commit checkpoint)

---

## Phase 1: Laravel Project Scaffolding

**Goal:** Fresh Laravel 12 project with dependencies installed, configured for our stack.

**Steps:**
1. Create new Laravel 12 project in the current repo (install into current directory)
2. Install dependencies:
   - `composer require laravel/ai` — Laravel AI SDK
   - Tailwind CSS 4 (included with Laravel 12 via Vite)
   - Alpine.js (add via npm)
3. Configure `.env`:
   - `DB_CONNECTION=sqlite` (create `database/database.sqlite`)
   - `OPENAI_API_KEY` placeholder
4. Publish AI SDK config and run migrations:
   - `php artisan vendor:publish --provider="Laravel\Ai\AiServiceProvider"`
   - `php artisan migrate`
5. Add Inter font via CDN in the main layout
6. Verify `php artisan serve` works with default welcome page

**Deliverable:** Working Laravel 12 app with all dependencies.

---

## Phase 2: Database — Models, Migrations, Seeders

**Goal:** Facility and Camp models with realistic NYC seed data.

### 2a: Migrations

**`create_facilities_table`**
```
id, name, slug, borough, neighborhood, address, latitude, longitude,
description, amenities (json), lunch_provided (boolean),
image_url, timestamps
```

**`create_camps_table`**
```
id, facility_id (FK), name, slug, category, description,
age_min, age_max, week_start (date), week_end (date),
schedule_type (enum), start_time, end_time,
price_cents, capacity, enrolled, waitlist_count,
lunch_provided (boolean), image_url, timestamps
```

### 2b: Models

**`Facility`** model:
- `amenities` cast to array
- `lunch_provided` cast to boolean
- `camps()` hasMany relationship
- Scope `inBorough($borough)`

**`Camp`** model:
- Casts for dates, booleans
- `facility()` belongsTo relationship
- Accessors: `is_full`, `spots_remaining`, `availability_status`, `formatted_price`
- Scopes: `forAge($age)`, `inCategory($category)`, `inWeek($date)`, `available()`, `inBorough($borough)`

### 2c: Seeders

**`FacilitySeeder`** — 18 facilities:
- 6 Manhattan (UWS, UES, Chelsea, Tribeca, Midtown, East Village)
- 6 Brooklyn (Park Slope, Williamsburg, DUMBO, Brooklyn Heights, Cobble Hill, Bushwick)
- 3 Queens (Astoria, Long Island City, Forest Hills)
- 2 Bronx (Riverdale, Pelham Bay)
- 1 Staten Island (St. George)

Mix of sizes:
- 5 small (1 category, 4-6 weeks only)
- 8 medium (2 categories, 7-9 weeks)
- 5 large (3-4 categories, all 10 weeks)

Each facility gets: realistic NYC name, real neighborhood coordinates, 2-5 amenities, ~40% with lunch.

**`CampSeeder`** — ~100 camps:
- Generated programmatically based on facility size
- Summer weeks: June 16, 23, 30; July 7, 14, 21, 28; Aug 4, 11, 18 (2026)
- Availability distribution: 60% available / 25% almost full / 15% waitlisted
- Price ranges by type: half-day $200-400, full-day $400-800
- Age ranges distributed across toddler/kid/tween/teen
- All 7 categories represented with realistic camp names per category

**Deliverable:** `php artisan migrate:fresh --seed` produces a populated database.

---

## Phase 3: AI Agent & Tools

**Goal:** CampRecommender agent that takes free text and returns structured camp plan.

### 3a: SearchCamps Tool

```php
// app/Ai/Tools/SearchCamps.php
// Schema: age (int), categories (string, comma-sep), borough (string, optional),
//         week_start (string, optional), price_max (int, optional),
//         schedule_type (string, optional)
// Returns: JSON string of matching camps (id, name, facility, category, age range,
//          price, availability, schedule, lunch)
// Limit: 20 results per query
```

Queries Camp model using Eloquent scopes. Returns compact JSON for LLM context efficiency.

### 3b: GetFacilityDetails Tool

```php
// app/Ai/Tools/GetFacilityDetails.php
// Schema: facility_id (int)
// Returns: Facility info + all camps at that facility
```

### 3c: CheckSiblingOverlap Tool

```php
// app/Ai/Tools/CheckSiblingOverlap.php
// Schema: camp_ids (string, comma-separated list of camp IDs)
// Returns: Grouped by week — which camps share a facility
```

### 3d: CampRecommender Agent

```php
// app/Ai/Agents/CampRecommender.php
// Implements: Agent, HasStructuredOutput, HasTools
// Provider: OpenAI (gpt-4o)
// Temperature: 0.7
// MaxSteps: 15 (needs multiple tool calls for multi-child)
//
// Instructions: System prompt from FRD section 5.1
// Tools: SearchCamps, GetFacilityDetails, CheckSiblingOverlap
// Schema: Matches FRD section 5.2
```

**Structured output schema** will define:
- `children` array with `name`, `age`, `summary`, and `weeks` array
- Each week has `week_start`, `week_label`, `primary_recommendation`, `alternative`
- Recommendations include: `camp_id`, `camp_name`, `facility_name`, `category`, `price_cents`, `schedule_type`, `availability_status`, `spots_remaining`, `waitlist_position`, `lunch_provided`, `reason`
- `sibling_overlaps` array
- `total_estimated_cost` integer
- `notes` string

### 3e: RecommendController

```php
// POST /api/recommend
// Accepts: { "prompt": "free text from user" }
// Validates prompt is present and <= 2000 chars
// Passes to CampRecommender agent
// Returns structured JSON response
```

**Deliverable:** `POST /api/recommend` with a text prompt returns structured camp recommendations.

---

## Phase 4: Frontend — Layout & Hero Section

**Goal:** HiSawyer-inspired landing page with hero and input form.

### 4a: Tailwind Config

- Extend with custom colors:
  - `teal-primary`: #0D9488 (teal-600)
  - `teal-light`: #CCFBF1 (teal-100)
  - `teal-dark`: #115E59 (teal-800)
- Inter font family via `@import` in CSS

### 4b: Main Layout (`welcome.blade.php`)

Single-page layout:
- Nav bar: Logo/brand "CampFinder AI" on left, minimal right side
- Hero section: gradient background (teal to white), large headline, subheadline
- Subtle SVG wave or curve divider between hero and content

### 4c: AI Input Form (Alpine.js component)

```
x-data="campFinder()"
```

Alpine component with:
- `prompt` — textarea bound value
- `loading` — boolean for loading state
- `results` — null or structured response
- `error` — error message
- `submitPrompt()` — fetch POST to `/api/recommend`

UI:
- Large textarea with placeholder text from FRD
- Character count indicator
- "Find Camps" button (teal, large, rounded)
- Loading state: pulsing animation, friendly message ("Planning your summer...")
- Error state: red alert with message

**Deliverable:** Beautiful landing page, form submits to API and receives response.

---

## Phase 5: Frontend — Swim Lane Results View

**Goal:** Render AI recommendations in a visual timeline.

### 5a: Swim Lane Container

- Horizontally scrollable container (10 weeks won't fit on screen)
- Sticky left column with child name/age labels
- Week headers across the top (Jun 16, Jun 23, ... Aug 18)
- Grid layout: `grid-template-columns: 200px repeat(10, 280px)`

### 5b: Camp Card Component

Each camp recommendation rendered as a card:
- **Top:** Category badge (color-coded pill)
- **Title:** Camp name (bold)
- **Subtitle:** Facility name (gray)
- **Details row:** Schedule type icon + time, price
- **Availability badge:**
  - Green: "X spots left"
  - Yellow: "Almost full!"
  - Red: "Waitlist (X ahead)"
- **Lunch icon:** Fork/knife if lunch provided
- **Reason:** Small italic text explaining why AI chose this
- **Alternative:** Collapsed "See alternative" toggle that shows the backup option

### 5c: Sibling Coordination Highlights

- When two children share a facility in the same week:
  - Both cards get a colored left border (e.g., purple)
  - Small "Same facility as [sibling name]" badge
- Sibling overlap summary section above the timeline

### 5d: Empty Week Styling

- Dashed border card with "Open week" text
- Subtle gray background

### 5e: Summary Section

Below the swim lane:
- Total estimated cost
- AI notes/tips
- "Try again" button to reset and enter new prompt

**Deliverable:** Full visual swim lane rendering of AI results.

---

## Phase 6: Polish & Integration Testing

**Goal:** End-to-end flow works smoothly, looks polished.

### 6a: Loading/Transition States

- Smooth Alpine.js transition from input → results
- Scroll to results after they load
- Fade-in animation on cards

### 6b: Error Handling

- API timeout handling (OpenAI can be slow — set generous timeout)
- Graceful error display if AI returns unexpected format
- "Try again" recovery flow

### 6c: Visual Polish

- Consistent spacing and alignment
- Category color mapping finalized:
  - Sports: blue
  - Arts: pink/rose
  - Performing Arts: purple
  - STEM: orange
  - Nature: green
  - Academic: indigo
  - General: gray
- Responsive adjustments for the swim lane (horizontal scroll works well)
- Favicon and page title

### 6d: Sample Prompts

Add 3-4 clickable example prompts below the textarea:
- "I have a 6-year-old who loves art and a 9-year-old into soccer. We're in Park Slope, budget ~$400/week."
- "Looking for STEM camps for my 10-year-old in Manhattan. Full day preferred, up to $600/week."
- "Three kids: ages 4, 7, and 11. We live in Astoria. Would love them at the same place when possible!"
- "My 8-year-old daughter loves theater and dance. She's shy so smaller camps preferred. Brooklyn area."

**Deliverable:** Polished, demo-ready application.

---

## Phase 7: Final Review & Documentation

**Goal:** Clean up, verify everything works, document setup.

### Steps:
1. Run `/simplify` on all changed code
2. Add a `README.md` with:
   - Project description
   - Setup instructions (composer install, npm install, .env config, migrate, seed, serve)
   - How to use
   - Tech stack
3. Final `php artisan migrate:fresh --seed` test
4. Full end-to-end test with 2-3 different prompts
5. Commit and push

**Deliverable:** Repo is clean, documented, and demo-ready.

---

## Execution Order & Dependencies

```
Phase 1 (Scaffolding)
  └── Phase 2 (Database)
        ├── Phase 3 (AI Agent) ← depends on models
        └── Phase 4 (Frontend Layout) ← independent of AI
              └── Phase 5 (Swim Lane) ← depends on API response shape from Phase 3
                    └── Phase 6 (Polish)
                          └── Phase 7 (Final)
```

Phases 3 and 4 can be worked in parallel since they're independent — but in a single-developer hackathon, we'll do them sequentially.

---

## Key Technical Decisions

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Database | SQLite | Zero config, prototype speed |
| LLM Model | GPT-4o via OpenAI | Best balance of speed + quality for structured output |
| Frontend framework | Alpine.js | Lightweight, no build complexity, Laravel native feel |
| CSS | Tailwind 4 | Ships with Laravel 12, rapid prototyping |
| Structured output | Laravel AI SDK `HasStructuredOutput` | Ensures consistent JSON shape for frontend |
| Agent tools | 3 focused tools | Gives LLM precise database access without raw SQL |
| No auth | Correct for prototype | Reduces scope significantly |
| No streaming (MVP) | Simpler implementation | Streaming is stretch goal |

---

## Risk Mitigation

| Risk | Mitigation |
|------|-----------|
| OpenAI structured output doesn't match schema | Validate response shape in controller, return error gracefully |
| Agent makes too many tool calls (slow) | Set MaxSteps(15), keep tool results compact |
| Seed data feels unrealistic | Use real NYC neighborhoods, realistic facility names, varied pricing |
| Swim lane is too wide on screen | Horizontal scroll with sticky child labels |
| AI recommends non-existent camp_ids | Validate camp_ids exist in response before rendering |
