<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CampFinder AI — Find the Perfect Summer Camp</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

<div x-data="campFinder()" class="h-screen flex flex-col overflow-hidden">
    {{-- Nav --}}
    <nav class="bg-white border-b border-gray-200 shrink-0">
        <div class="max-w-full mx-auto px-4 h-12 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V7l9-4 9 4v14M3 21h18M9 21V11h6v10"/>
                </svg>
                <span class="text-lg font-bold text-gray-900">Camp<span class="text-teal-600">Finder</span> AI</span>
            </div>
            <span class="text-xs text-gray-400">NYC Summer 2026</span>
        </div>
    </nav>

    {{-- Hero / Input (collapses when results shown) --}}
    <div x-show="!results" class="flex-1 flex items-start justify-center overflow-auto bg-gradient-to-b from-teal-50 via-teal-50/30 to-gray-50">
        <div class="w-full max-w-2xl mx-auto px-4 pt-12 pb-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
                Find the Perfect <span class="text-teal-600">Summer Camp</span>
            </h1>
            <p class="text-base text-gray-600 mb-8 max-w-xl mx-auto">
                Tell us about your children and we'll plan their entire summer with personalized camp recommendations across NYC.
            </p>

            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 text-left">
                <label for="prompt" class="block text-sm font-semibold text-gray-700 mb-2">
                    Describe your kids and what you're looking for
                </label>
                <textarea
                    id="prompt"
                    x-model="prompt"
                    @keydown.meta.enter="submitPrompt()"
                    rows="3"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent resize-none"
                    placeholder="I have a 6-year-old who loves art and a 9-year-old who's into soccer. We live in Park Slope and our budget is around $400/week per kid."
                    :disabled="loading"
                ></textarea>

                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-gray-400" x-text="prompt.length + '/2000'"></span>
                    <button
                        @click="submitPrompt()"
                        :disabled="loading || !prompt.trim()"
                        class="bg-teal-600 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold px-6 py-2 rounded-xl text-sm transition-colors"
                    >
                        <span x-show="!loading">Find Camps</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Planning...
                        </span>
                    </button>
                </div>

                <div class="mt-4 pt-3 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-2">Try an example:</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="example in examples" :key="example">
                            <button
                                @click="prompt = example"
                                class="text-xs bg-gray-50 hover:bg-teal-50 text-gray-600 hover:text-teal-700 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-teal-200 transition-colors"
                                x-text="example.substring(0, 55) + '...'"
                                :disabled="loading"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading overlay when retrying with results visible --}}
    <div x-show="loading && results" x-cloak class="fixed inset-0 bg-white/60 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl shadow-lg px-6 py-4 flex items-center gap-3">
            <svg class="animate-spin w-5 h-5 text-teal-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
            </svg>
            <span class="text-sm font-medium text-gray-700">Re-planning summer...</span>
        </div>
    </div>

    {{-- Error --}}
    <div x-show="error" x-cloak class="px-4 py-2 shrink-0">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-lg flex items-center justify-between text-sm">
            <span x-text="error"></span>
            <button @click="error = null" class="text-red-400 hover:text-red-600 ml-2">&times;</button>
        </div>
    </div>

    {{-- Results Grid --}}
    <template x-if="results">
        <div class="flex-1 flex flex-col min-h-0">
            {{-- Toolbar --}}
            <div class="bg-white border-b border-gray-200 px-4 py-2 shrink-0">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <h2 class="text-sm font-bold text-gray-900">Summer Camp Plan</h2>
                        <div class="flex items-center gap-1">
                            <span class="text-xs text-gray-500">Total:</span>
                            <span class="text-sm font-bold text-teal-700" x-text="formatPrice(results.total_estimated_cost_cents)"></span>
                        </div>
                        <template x-if="hasLockedOrBlocked()">
                            <div class="flex items-center gap-2">
                                <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium"
                                      x-text="countLocked() + ' locked'"></span>
                                <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full font-medium"
                                      x-text="countBlocked() + ' blocked'"></span>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="hasLockedOrBlocked()">
                            <button @click="retryWithConstraints()"
                                    :disabled="loading"
                                    class="text-xs bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white font-semibold px-4 py-1.5 rounded-lg transition-colors">
                                Re-plan Unlocked Weeks
                            </button>
                        </template>
                        <button @click="clearConstraints()"
                                x-show="hasLockedOrBlocked()"
                                class="text-xs bg-white border border-gray-200 hover:border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg transition-colors">
                            Clear All
                        </button>
                        <button @click="resetForm()" class="text-xs bg-white border border-gray-200 hover:border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg transition-colors">
                            Start Over
                        </button>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-1" x-text="results.notes"></p>
            </div>

            {{-- Grid --}}
            <div class="flex-1 overflow-auto min-h-0">
                <div class="grid min-h-full" :style="gridStyle()">
                    {{-- Header row: empty corner + week labels --}}
                    <div class="sticky left-0 top-0 z-30 bg-gray-100 border-b border-r border-gray-200 px-2 py-2 flex items-end">
                        <span class="text-xs font-semibold text-gray-500">Children</span>
                    </div>
                    <template x-for="(weekStart, wIdx) in weekStarts" :key="weekStart">
                        <div class="sticky top-0 z-20 bg-gray-100 border-b border-gray-200 px-1 py-2 text-center"
                             :class="wIdx < weekStarts.length - 1 ? 'border-r border-gray-100' : ''">
                            <div class="text-xs font-bold text-gray-700" x-text="'Wk ' + (wIdx + 1)"></div>
                            <div class="text-[10px] text-gray-500" x-text="shortWeekLabel(weekStart)"></div>
                        </div>
                    </template>

                    {{-- Child rows — use display:contents wrapper to keep grid flat --}}
                    <template x-for="(child, cIdx) in results.children" :key="cIdx">
                        <div class="contents">
                            {{-- Child label (sticky left) --}}
                            <div class="sticky left-0 z-10 bg-white border-b border-r border-gray-200 px-2 py-2 flex items-start gap-2">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white font-bold text-xs shrink-0 mt-0.5"
                                     :class="childColors[cIdx % childColors.length]">
                                    <span x-text="child.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-xs font-bold text-gray-900 truncate" x-text="child.name"></div>
                                    <div class="text-[10px] text-gray-500" x-text="'Age ' + child.age"></div>
                                </div>
                            </div>

                            {{-- Week cells --}}
                            <template x-for="(week, wIdx) in child.weeks" :key="week.week_start">
                                <div class="border-b border-gray-100 p-1 relative group"
                                     :class="{
                                         'bg-gray-50': week.blocked,
                                         'bg-amber-50/50': isLocked(cIdx, week.week_start),
                                         'bg-white': !week.blocked && !isLocked(cIdx, week.week_start),
                                         'border-r border-gray-100': wIdx < weekStarts.length - 1,
                                     }">

                                    {{-- Blocked week --}}
                                    <template x-if="week.blocked">
                                        <div class="h-full min-h-[80px] flex flex-col items-center justify-center">
                                            <div class="text-xs text-gray-400 font-medium">Off</div>
                                            <button @click="toggleBlock(cIdx, week.week_start)"
                                                    class="mt-1 text-[10px] text-teal-600 hover:text-teal-800">Unblock</button>
                                        </div>
                                    </template>

                                    {{-- Camp card --}}
                                    <template x-if="!week.blocked && week.primary_recommendation">
                                        <div class="min-h-[80px]">
                                            {{-- Action buttons (visible on hover) --}}
                                            <div class="absolute top-1 right-1 flex gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity z-10">
                                                <button @click="toggleLock(cIdx, week)"
                                                        class="p-0.5 rounded hover:bg-gray-100"
                                                        :class="isLocked(cIdx, week.week_start) ? 'text-amber-500' : 'text-gray-400'">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <template x-if="isLocked(cIdx, week.week_start)">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                                        </template>
                                                        <template x-if="!isLocked(cIdx, week.week_start)">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                                        </template>
                                                    </svg>
                                                </button>
                                                <button @click="toggleBlock(cIdx, week.week_start)"
                                                        class="p-0.5 rounded text-gray-400 hover:bg-gray-100 hover:text-red-500">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            {{-- Lock indicator --}}
                                            <template x-if="isLocked(cIdx, week.week_start)">
                                                <div class="absolute top-1 left-1">
                                                    <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                    </svg>
                                                </div>
                                            </template>

                                            {{-- Sibling overlap indicator --}}
                                            <template x-if="isSiblingOverlap(week, child, cIdx)">
                                                <div class="absolute top-1 left-1" :class="isLocked(cIdx, week.week_start) ? 'left-5' : ''">
                                                    <svg class="w-3 h-3 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                                    </svg>
                                                </div>
                                            </template>

                                            {{-- Category dot + name --}}
                                            <div class="flex items-start gap-1 mb-1 pr-6">
                                                <span class="w-2 h-2 rounded-full mt-1 shrink-0"
                                                      :class="categoryDot[week.primary_recommendation.category] || 'bg-gray-400'"></span>
                                                <span class="text-xs font-semibold text-gray-900 leading-tight line-clamp-2"
                                                      x-text="week.primary_recommendation.camp_name"></span>
                                            </div>

                                            {{-- Facility --}}
                                            <div class="text-[10px] text-gray-500 truncate" x-text="week.primary_recommendation.facility_name"></div>

                                            {{-- Price + schedule --}}
                                            <div class="flex items-center justify-between mt-1">
                                                <span class="text-[10px] text-gray-500" x-text="shortSchedule(week.primary_recommendation.schedule_type)"></span>
                                                <span class="text-xs font-bold text-gray-900" x-text="formatPrice(week.primary_recommendation.price_cents)"></span>
                                            </div>

                                            {{-- Availability badge --}}
                                            <div class="mt-1">
                                                <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full"
                                                      :class="{
                                                          'bg-green-100 text-green-700': week.primary_recommendation.availability_status === 'available',
                                                          'bg-yellow-100 text-yellow-700': week.primary_recommendation.availability_status === 'almost_full',
                                                          'bg-red-100 text-red-700': week.primary_recommendation.availability_status === 'waitlist'
                                                      }"
                                                      x-text="shortAvail(week.primary_recommendation)"></span>
                                                <template x-if="week.primary_recommendation.lunch_provided">
                                                    <span class="text-[10px] text-gray-400 ml-1">+ lunch</span>
                                                </template>
                                            </div>

                                            {{-- Alt toggle --}}
                                            <template x-if="week.alternative && !isLocked(cIdx, week.week_start)">
                                                <div class="mt-1 pt-1 border-t border-gray-100">
                                                    <button @click="swapToAlt(cIdx, wIdx)"
                                                            class="text-[10px] text-teal-600 hover:text-teal-800 font-medium">
                                                        Swap alt
                                                    </button>
                                                    <div class="text-[10px] text-gray-400 truncate" x-text="week.alternative.camp_name"></div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>

                                    {{-- Empty week --}}
                                    <template x-if="!week.blocked && !week.primary_recommendation">
                                        <div class="min-h-[80px] flex flex-col items-center justify-center">
                                            <div class="text-[10px] text-gray-300">No match</div>
                                            <button @click="toggleBlock(cIdx, week.week_start)"
                                                    class="mt-1 text-[10px] text-gray-400 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-opacity">Block</button>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function campFinder() {
    return {
        prompt: '',
        loading: false,
        results: null,
        parsedCriteria: null,
        error: null,
        blockedWeeks: {},   // { childIdx: [weekStart, ...] }
        lockedCamps: {},    // { childIdx: { weekStart: recommendation } }

        weekStarts: [
            '2026-06-15', '2026-06-22', '2026-06-29',
            '2026-07-06', '2026-07-13', '2026-07-20', '2026-07-27',
            '2026-08-03', '2026-08-10', '2026-08-17',
        ],

        examples: [
            "I have a 6-year-old who loves art and a 9-year-old who's into soccer. We live in Park Slope and our budget is around $400/week per kid.",
            "Looking for STEM camps for my 10-year-old in Manhattan. Full day preferred, budget up to $600/week.",
            "Three kids: ages 4, 7, and 11. We live in Astoria. Would love them at the same place when possible!",
            "My 8-year-old daughter loves theater and dance. Brooklyn area, up to $500/week.",
        ],

        childColors: ['bg-teal-600', 'bg-indigo-600', 'bg-rose-600', 'bg-amber-600'],

        categoryDot: {
            sports: 'bg-blue-500',
            arts: 'bg-pink-500',
            performing_arts: 'bg-purple-500',
            stem: 'bg-orange-500',
            nature: 'bg-green-500',
            academic: 'bg-indigo-500',
            general: 'bg-gray-400',
        },

        gridStyle() {
            const cols = this.results ? this.results.children[0]?.weeks.length || 10 : 10;
            return `grid-template-columns: 100px repeat(${cols}, minmax(0, 1fr)); grid-template-rows: auto repeat(${this.results?.children.length || 1}, 1fr);`;
        },

        async submitPrompt() {
            if (!this.prompt.trim() || this.loading) return;

            this.loading = true;
            this.error = null;
            this.results = null;
            this.parsedCriteria = null;
            this.blockedWeeks = {};
            this.lockedCamps = {};

            try {
                const response = await fetch('/api/recommend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt: this.prompt }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Something went wrong');
                }

                this.results = data.data;
                this.parsedCriteria = data.parsed_criteria;
            } catch (err) {
                this.error = err.message || 'Failed to get recommendations.';
            } finally {
                this.loading = false;
            }
        },

        async retryWithConstraints() {
            if (this.loading || !this.parsedCriteria) return;

            this.loading = true;
            this.error = null;

            try {
                const response = await fetch('/api/recommend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        prompt: this.prompt,
                        parsed_criteria: this.parsedCriteria,
                        blocked_weeks: this.blockedWeeks,
                        locked_camps: this.lockedCamps,
                    }),
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(data.error || 'Something went wrong');
                }

                this.results = data.data;
            } catch (err) {
                this.error = err.message || 'Failed to re-plan.';
            } finally {
                this.loading = false;
            }
        },

        toggleBlock(childIdx, weekStart) {
            if (!this.blockedWeeks[childIdx]) {
                this.blockedWeeks[childIdx] = [];
            }

            const idx = this.blockedWeeks[childIdx].indexOf(weekStart);
            if (idx >= 0) {
                this.blockedWeeks[childIdx].splice(idx, 1);
            } else {
                this.blockedWeeks[childIdx].push(weekStart);
                // Remove any lock for this week if blocking
                if (this.lockedCamps[childIdx]) {
                    delete this.lockedCamps[childIdx][weekStart];
                }
            }

            // Update local state immediately
            const child = this.results.children[childIdx];
            const week = child.weeks.find(w => w.week_start === weekStart);
            if (week) {
                week.blocked = !week.blocked;
                if (week.blocked) {
                    week.primary_recommendation = null;
                    week.alternative = null;
                }
            }

            this.recalcCost();
        },

        toggleLock(childIdx, week) {
            if (!this.lockedCamps[childIdx]) {
                this.lockedCamps[childIdx] = {};
            }

            if (this.lockedCamps[childIdx][week.week_start]) {
                delete this.lockedCamps[childIdx][week.week_start];
            } else if (week.primary_recommendation) {
                this.lockedCamps[childIdx][week.week_start] = week.primary_recommendation;
            }
        },

        isLocked(childIdx, weekStart) {
            return !!(this.lockedCamps[childIdx] && this.lockedCamps[childIdx][weekStart]);
        },

        swapToAlt(childIdx, weekIdx) {
            const week = this.results.children[childIdx].weeks[weekIdx];
            if (!week.alternative) return;

            const temp = week.primary_recommendation;
            week.primary_recommendation = week.alternative;
            week.alternative = temp;

            // If this week was locked, update the lock
            if (this.isLocked(childIdx, week.week_start)) {
                this.lockedCamps[childIdx][week.week_start] = week.primary_recommendation;
            }

            this.recalcCost();
        },

        recalcCost() {
            let total = 0;
            if (this.results) {
                for (const child of this.results.children) {
                    for (const week of child.weeks) {
                        if (week.primary_recommendation && !week.blocked) {
                            total += week.primary_recommendation.price_cents;
                        }
                    }
                }
                this.results.total_estimated_cost_cents = total;
            }
        },

        hasLockedOrBlocked() {
            return this.countLocked() > 0 || this.countBlocked() > 0;
        },

        countLocked() {
            let count = 0;
            for (const childIdx in this.lockedCamps) {
                count += Object.keys(this.lockedCamps[childIdx]).length;
            }
            return count;
        },

        countBlocked() {
            let count = 0;
            for (const childIdx in this.blockedWeeks) {
                count += this.blockedWeeks[childIdx].length;
            }
            return count;
        },

        clearConstraints() {
            this.blockedWeeks = {};
            this.lockedCamps = {};
            // Unblock all weeks in local state
            if (this.results) {
                for (const child of this.results.children) {
                    for (const week of child.weeks) {
                        week.blocked = false;
                    }
                }
            }
        },

        resetForm() {
            this.results = null;
            this.parsedCriteria = null;
            this.error = null;
            this.blockedWeeks = {};
            this.lockedCamps = {};
        },

        formatPrice(cents) {
            if (!cents && cents !== 0) return '';
            return '$' + (cents / 100).toLocaleString('en-US', { maximumFractionDigits: 0 });
        },

        shortWeekLabel(weekStart) {
            const d = new Date(weekStart + 'T00:00:00');
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        shortSchedule(type) {
            const map = { full_day: 'Full', half_day_am: 'AM', half_day_pm: 'PM' };
            return map[type] || type;
        },

        shortAvail(rec) {
            if (rec.availability_status === 'available') return rec.spots_remaining + ' spots';
            if (rec.availability_status === 'almost_full') return rec.spots_remaining + ' left!';
            return 'Waitlist';
        },

        isSiblingOverlap(week, child, childIdx) {
            if (!this.results?.sibling_overlaps || !week.primary_recommendation) return false;
            return this.results.sibling_overlaps.some(o =>
                o.week_start === week.week_start &&
                o.facility_name === week.primary_recommendation.facility_name
            );
        },
    };
}
</script>

<style>
    [x-cloak] { display: none !important; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

</body>
</html>
