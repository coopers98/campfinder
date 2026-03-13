# CampFinder AI - Functional Requirements Document

**Version:** 1.0
**Date:** 2026-03-13
**Status:** Draft
**Context:** Innovation/hackathon day prototype

---

## 1. Overview

CampFinder AI is a prototype web application that helps parents find and plan summer activity camps for their children in NYC. Parents enter free-text descriptions of their kids' ages, interests, location, and budget, and an AI agent returns personalized camp recommendations displayed in a visual swim-lane timeline across the summer.

**No authentication required** — this is a demo/prototype site.

---

## 2. Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 12 |
| Frontend | Blade + Alpine.js + Tailwind CSS |
| AI/LLM | Laravel AI SDK (`laravel/ai`) with OpenAI provider |
| Database | SQLite (prototype simplicity) |
| Design | HiSawyer-inspired aesthetic (clean, modern, teal/green accents, rounded cards, friendly sans-serif) |

---

## 3. Data Model

### 3.1 Facilities

Represents a physical location/organization that runs camps.

| Field | Type | Description |
|-------|------|-------------|
| id | int | PK |
| name | string | e.g., "Brooklyn Arts Center" |
| slug | string | URL-friendly name |
| borough | enum | Manhattan, Brooklyn, Queens, Bronx, Staten Island |
| neighborhood | string | e.g., "Park Slope", "Upper West Side" |
| address | string | Full street address |
| latitude | decimal | For distance calculations |
| longitude | decimal | For distance calculations |
| description | text | About the facility |
| amenities | json | e.g., ["indoor gym", "pool", "outdoor field"] |
| lunch_provided | boolean | Whether facility provides lunch |
| image_url | string | Facility photo |
| created_at | timestamp | |
| updated_at | timestamp | |

### 3.2 Camps

A specific camp program offered by a facility during a given week.

| Field | Type | Description |
|-------|------|-------------|
| id | int | PK |
| facility_id | int | FK to facilities |
| name | string | e.g., "Junior Soccer Stars" |
| slug | string | URL-friendly name |
| category | enum | See 3.4 |
| description | text | What kids do at this camp |
| age_min | int | Minimum age (e.g., 4) |
| age_max | int | Maximum age (e.g., 7) |
| week_start | date | Monday of the camp week |
| week_end | date | Friday of the camp week |
| schedule_type | enum | full_day, half_day_am, half_day_pm |
| start_time | time | e.g., 9:00 AM |
| end_time | time | e.g., 3:00 PM |
| price_cents | int | Price in cents |
| capacity | int | Max enrollment |
| enrolled | int | Current enrollment count |
| waitlist_count | int | Number on waitlist (only if enrolled >= capacity) |
| lunch_provided | boolean | Overrides facility default if set |
| image_url | string | Camp-specific photo |
| created_at | timestamp | |
| updated_at | timestamp | |

**Derived fields:**
- `is_full`: `enrolled >= capacity`
- `spots_remaining`: `capacity - enrolled`
- `availability_status`: "available", "almost_full" (<=3 spots), "waitlist"

### 3.3 Facility Size Variations (Seed Data Strategy)

| Size | Camps/Week | Examples |
|------|-----------|---------|
| Small | 0-1 per week, not every week | "Sunshine Art Studio" — runs 4 weeks of art camp only |
| Medium | 1-2 per week, most weeks | "Park Slope Community Center" — sports + arts |
| Large | 3-4 per week, all summer | "NYC Kids Academy" — full program across categories |

### 3.4 Camp Categories

- **Sports & Athletics** — soccer, basketball, swimming, gymnastics, martial arts
- **Arts & Crafts** — painting, sculpture, ceramics, fiber arts
- **Performing Arts** — theater, dance, music, film
- **STEM & Technology** — coding, robotics, science experiments, engineering
- **Nature & Outdoors** — nature exploration, gardening, urban farming
- **Academic** — reading, writing, math enrichment, language
- **General / Multi-Activity** — mix of activities, day camp style

### 3.5 Seed Data Requirements

- **15-20 facilities** across all 5 boroughs (weighted toward Manhattan & Brooklyn)
- **80-120 camp listings** spanning June 16 – August 22, 2026 (10 weeks of summer)
- Realistic availability distribution:
  - ~60% available (various spots remaining)
  - ~25% almost full (1-3 spots)
  - ~15% full with waitlist (waitlist 1-8 people)
- Age ranges: toddler (3-5), kid (5-8), tween (8-12), teen (12-15), mixed
- Price ranges: $200-$800/week depending on full/half day, category, and facility
- Lunch provided: ~40% of camps
- Mix of full-day and half-day options

---

## 4. User Interface

### 4.1 Design System (HiSawyer-Inspired)

- **Colors:** White background, teal/green primary (#0D9488 or similar), warm grays, soft card shadows
- **Cards:** Rounded corners (lg), subtle shadow, white bg, hover elevation
- **Typography:** Clean sans-serif (Inter or system font stack), friendly tone
- **Spacing:** Generous padding, airy layout
- **Images:** Rounded, consistent aspect ratio in cards
- **Badges:** Colored pills for categories, availability status

### 4.2 Page: Landing / Home

Single-page app feel. Sections top to bottom:

1. **Hero Section**
   - Headline: "Find the Perfect Summer Camp for Your Kids"
   - Subheadline: "Tell us about your children and we'll plan their summer"
   - HiSawyer-style friendly illustration or gradient background

2. **AI Input Section**
   - Large, inviting text area with placeholder text:
     *"Tell us about your kids! For example: I have a 6-year-old who loves art and a 9-year-old who's into soccer. We live in Park Slope and our budget is around $400/week per kid. They'd love to be at the same place if possible!"*
   - "Find Camps" submit button (teal, prominent)
   - Loading state with friendly animation while AI processes

3. **Results Section** (appears after AI response)
   - See 4.3 Swim Lane View

### 4.3 Swim Lane Timeline View

**Layout:**
- **X-axis (columns):** Weeks of summer (June 16 – Aug 22, 10 columns)
- **Y-axis (rows/lanes):** One lane per child
- Each lane is labeled with the child's name/description (e.g., "Emma, age 6" / "Jake, age 9")

**Camp Cards in Timeline:**
Each recommended camp appears as a card placed in its corresponding week column:
- Camp name
- Facility name
- Time (full day / half day AM/PM)
- Price
- Category badge (color-coded)
- Availability badge: green "Available (X spots)", yellow "Almost Full!", red "Waitlist (X ahead)"
- Lunch icon if lunch is provided
- If two siblings are at the same facility in the same week, visually connect/highlight them

**Sibling Coordination Indicators:**
- When kids share a facility in the same week: a visual connector or shared highlight color
- "Same facility" badge on connected cards

**Empty Weeks:**
- Show as open slots with a subtle dashed border: "No camp this week" or "Open week"

### 4.4 Camp Detail (Stretch Goal)

Click a camp card to see a slide-over or modal with:
- Full description
- Facility details & amenities
- Map placeholder
- Full availability info
- Other camps at the same facility that week

---

## 5. AI Agent Design

### 5.1 Agent: CampRecommender

**Implementation:** Laravel AI SDK Agent class with structured output and tool use.

**System Instructions:**
You are a helpful summer camp advisor for NYC families. Given information about one or more children (ages, interests, location, budget, scheduling preferences), recommend the best camp options for each child across the summer weeks. Prioritize:
1. Age-appropriate camps
2. Matching interests/categories to the child
3. Budget constraints
4. Geographic preference (closer is better)
5. Sibling coordination — try to place siblings at the same facility or nearby when possible
6. Availability — prefer camps with open spots, note waitlisted options as alternatives

**Tools Available to Agent:**

1. **SearchCamps** — Query the camps database with filters:
   - age (child's age)
   - categories (preferred categories)
   - borough/neighborhood
   - week_start
   - price_max
   - schedule_type
   - availability (available, almost_full, waitlist)

2. **GetFacilityDetails** — Get full facility info including all camps offered

3. **CheckSiblingOverlap** — Given multiple children's recommended camps, find weeks where they could attend the same facility

### 5.2 Structured Output Schema

```json
{
  "children": [
    {
      "name": "string",
      "age": "integer",
      "summary": "string (brief explanation of recommendations)",
      "weeks": [
        {
          "week_start": "date",
          "week_label": "string (e.g., 'Week 1: Jun 16-20')",
          "primary_recommendation": {
            "camp_id": "integer",
            "camp_name": "string",
            "facility_name": "string",
            "category": "string",
            "price_cents": "integer",
            "schedule_type": "string",
            "availability_status": "string",
            "spots_remaining": "integer|null",
            "waitlist_position": "integer|null",
            "lunch_provided": "boolean",
            "reason": "string (why this camp was chosen)"
          },
          "alternative": {
            "...same structure..."
          }
        }
      ]
    }
  ],
  "sibling_overlaps": [
    {
      "week_start": "date",
      "facility_name": "string",
      "children": ["string (child names)"]
    }
  ],
  "total_estimated_cost": "integer (cents)",
  "notes": "string (general advice, waitlist tips, etc.)"
}
```

### 5.3 Request Flow

1. User submits free text → POST `/api/recommend`
2. Controller passes text to `CampRecommender` agent
3. Agent uses tools to query database, evaluate options
4. Agent returns structured JSON response
5. Frontend receives JSON, renders swim lane view with Alpine.js

---

## 6. API Endpoints

| Method | Path | Description |
|--------|------|-------------|
| GET | `/` | Landing page (Blade view) |
| POST | `/api/recommend` | Submit free text, get AI recommendations |
| GET | `/api/camps` | List all camps (for browse, optional) |
| GET | `/api/camps/{id}` | Camp detail (stretch goal) |
| GET | `/api/facilities` | List facilities (stretch goal) |

---

## 7. Scope Summary

### MVP (Hackathon Day)
- [x] SQLite database with seed data (15-20 facilities, 80-120 camps)
- [x] Landing page with hero + AI text input
- [x] AI agent with structured output for camp recommendations
- [x] Swim lane timeline view for results
- [x] Multi-child support with sibling coordination
- [x] Availability/waitlist display
- [x] HiSawyer-inspired visual design
- [x] Lunch provided indicator

### Stretch Goals
- [ ] Follow-up conversation (refine recommendations)
- [ ] Camp detail modal/slide-over
- [ ] Streaming AI response with progressive rendering
- [ ] Browse all camps grid view
- [ ] Filter/sort camps manually
- [ ] Map view of facilities

### Out of Scope
- Authentication / user accounts
- Booking / payment
- Saved searches / favorites
- Admin panel
- Real data scraping from HiSawyer
- Mobile-responsive design (nice to have but not required)

---

## 8. File Structure (Planned)

```
app/
  Ai/
    Agents/
      CampRecommender.php
    Tools/
      SearchCamps.php
      GetFacilityDetails.php
      CheckSiblingOverlap.php
  Models/
    Facility.php
    Camp.php
  Http/
    Controllers/
      RecommendController.php
      CampController.php (stretch)
database/
  migrations/
    create_facilities_table.php
    create_camps_table.php
  seeders/
    FacilitySeeder.php
    CampSeeder.php
resources/
  views/
    welcome.blade.php (main SPA-like page)
    components/
      hero.blade.php
      input-form.blade.php
      swim-lane.blade.php
      camp-card.blade.php
routes/
  web.php
  api.php
docs/
  FRD.md (this file)
```
