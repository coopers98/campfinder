# CampFinder AI

**A hackathon prototype for AI-powered summer camp discovery in NYC.**

Parents describe their children and preferences in plain English, and CampFinder AI builds a personalized 10-week summer camp plan across all five boroughs.

## Goal

Finding summer camps in NYC is overwhelming. Parents juggle dozens of providers, age restrictions, schedules, locations, and availability — multiplied by the number of children and weeks in the summer. CampFinder AI aims to collapse that research from days into seconds.

The prototype demonstrates how a natural language interface backed by structured AI parsing and a smart matching engine can generate a complete, interactive summer plan that parents can refine in real time.

## How It Works

### Two-Stage Architecture

The system uses a deliberate two-stage design to balance intelligence with speed:

**Stage 1 — LLM Parsing (2-3s)**
A single call to `gpt-4o-mini` via Laravel AI extracts structured criteria from free-text input: children's names, ages, interests, borough, budget, schedule preference, and whether siblings should attend the same facility. The LLM acts purely as a parser — no recommendations, no camp knowledge.

**Stage 2 — PHP Matching Engine (instant)**
A deterministic PHP service (`CampMatcher`) queries the database, scores every candidate camp, and assembles the full plan. This avoids the latency, cost, and unpredictability of asking an LLM to reason over hundreds of camp options. Scoring factors include:

- Category match (primary interest 40pts, secondary 30pts, general 10pts)
- Borough proximity (20pts) with Haversine distance calculation
- Availability status (available 15pts, almost full 8pts, waitlist 0pts)
- Schedule type and amenities (lunch, full-day bonuses)

Results are sorted with interest matches first, then by score, so parents always see relevant camps before "also available" alternatives.

### Sibling-Aware Planning

When multiple children are present, the engine runs a second pass after initial scoring to find weeks where a shared facility has age-appropriate camps for all siblings. It auto-selects these options and highlights them with purple badges (`SAME` for identical camps, `FAC` for same facility).

### Interactive Refinement

The plan isn't static. Parents can:

- **Select** between 3-5 ranked options per week per child
- **Lock** a preferred camp to preserve it during re-planning
- **Block** a week per child (vacation, travel, etc.)
- **Re-plan** unlocked weeks with fresh options (previously shown camps are excluded)

All refinement happens client-side or via the same fast PHP engine — no additional LLM calls.

## Tech Stack

- **Laravel 12** on Docker (Laravel Sail)
- **Laravel AI SDK** (`laravel/ai` v0.3.0) with OpenAI provider for structured output parsing
- **Alpine.js** reactive frontend — single-page feel without a JS build framework
- **Tailwind CSS 4** with custom Sawyer brand palette (`#ff5a52`)
- **SQLite** database with seeded sample data (27 facilities, ~1,500 camp sessions)

## Camp Categories

The prototype covers 10 camp types across NYC:

| Category | Emoji | Example Camps |
|----------|-------|---------------|
| Sports | ⚽ | Gotham Goal Strikers, Brooklyn Ballers Academy |
| Arts | 🎨 | Tiny Brushstrokes Studio, Clay Borough Pottery |
| Performing Arts | 🎭 | Broadway Bootcamp Jr., The Rhythm Hive |
| STEM | 🔬 | CodeCraft Academy, BotBuilder Workshop |
| Nature | 🌿 | Urban Wilderness Rangers, Tide Pool Explorers |
| Academic | 📚 | Page Turners Book Lab, Checkmate Chess Intensive |
| Martial Arts | 🥋 | Little Dragons Karate, Ninja Academy NYC |
| Equestrian | 🐴 | Saddle Up Stables Camp, Bronx Pony Club |
| Pets & Animals | 🐾 | Pawsitive Kids Animal Camp, Junior Vet Academy |
| General | ☀️ | Camp Kaleidoscope, The Great Summer Mashup |

## UI Design

The results view is a month-paginated calendar grid (June / July / August) with:

- Child rows on the left, week columns across
- Camp options as selectable cards with emoji, price, distance, and availability
- Hover tooltips with full camp details (facility, neighborhood, distance, schedule, reason)
- Visual dividers between interest-matched camps and "also available" alternatives
- Purple highlights for sibling facility overlap opportunities
- Lock/block controls per cell for plan refinement

## Running Locally

```bash
# Clone and install
git clone <repo-url> && cd campfinder
composer install
cp .env.example .env

# Start containers and seed data
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail npm install && ./vendor/bin/sail npm run build

# Visit http://localhost:8080
```

Requires an OpenAI API key in `.env` (`OPENAI_API_KEY=...`) for the natural language parsing stage.

## Sample Data Explorer

Visit `/data` to browse all seeded facilities and camp sessions with filters for borough, category, week, and child age. Useful for prototype reviewers to verify the underlying data.

## What This Demonstrates

- Natural language as the primary interface for complex multi-variable search
- Hybrid AI architecture: LLM for understanding, deterministic code for execution
- Sub-5-second response times for a task that would take parents hours manually
- Interactive plan refinement without round-tripping to an LLM
- Sibling coordination as a first-class feature, not an afterthought
