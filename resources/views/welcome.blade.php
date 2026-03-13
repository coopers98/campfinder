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
            <div class="flex items-center gap-4">
                <a href="/data" class="text-xs text-teal-600 hover:text-teal-800 font-medium">Sample Data</a>
                <span class="text-xs text-gray-400">NYC Summer 2026</span>
            </div>
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

    {{-- Loading overlay --}}
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
                            <span class="text-sm font-bold text-teal-700" x-text="formatPrice(calcTotal())"></span>
                        </div>
                        <template x-if="hasLockedOrBlocked()">
                            <div class="flex items-center gap-2">
                                <template x-if="countLocked() > 0">
                                    <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium"
                                          x-text="countLocked() + ' locked'"></span>
                                </template>
                                <template x-if="countBlocked() > 0">
                                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-0.5 rounded-full font-medium"
                                          x-text="countBlocked() + ' off'"></span>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div class="flex items-center gap-2">
                        <template x-if="hasLockedOrBlocked()">
                            <button @click="retryWithConstraints()"
                                    :disabled="loading"
                                    class="text-xs bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white font-semibold px-4 py-1.5 rounded-lg transition-colors">
                                Re-plan Unlocked
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
                    {{-- Header row --}}
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

                    {{-- Child rows --}}
                    <template x-for="(child, cIdx) in results.children" :key="cIdx">
                        <div class="contents">
                            {{-- Child label --}}
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
                                <div class="border-b border-r border-gray-100 p-0.5 relative group/cell"
                                     :class="{
                                         'bg-gray-50': week.blocked,
                                         'bg-white': !week.blocked,
                                     }">

                                    {{-- Blocked --}}
                                    <template x-if="week.blocked">
                                        <div class="h-full min-h-[100px] flex flex-col items-center justify-center">
                                            <div class="text-xs text-gray-400 font-medium">Off</div>
                                            <button @click="toggleBlock(cIdx, week.week_start)"
                                                    class="mt-1 text-[10px] text-teal-600 hover:text-teal-800">Unblock</button>
                                        </div>
                                    </template>

                                    {{-- Options list --}}
                                    <template x-if="!week.blocked && week.options && week.options.length > 0">
                                        <div class="space-y-0.5 min-h-[100px]">
                                            {{-- Block button in corner --}}
                                            <button @click="toggleBlock(cIdx, week.week_start)"
                                                    class="absolute top-0.5 right-0.5 p-0.5 rounded text-gray-300 hover:text-red-500 hover:bg-gray-100 z-20 opacity-0 group-hover/cell:opacity-100 transition-opacity">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>

                                            <template x-for="(opt, oIdx) in week.options" :key="opt.camp_id">
                                                <div class="relative"
                                                     x-data="{ showTip: false }"
                                                     @mouseenter="showTip = true" @mouseleave="showTip = false">
                                                    <div @click="selectOption(cIdx, wIdx, oIdx)"
                                                         class="rounded p-1 cursor-pointer transition-all text-left"
                                                         :class="{
                                                             'bg-teal-50 ring-1 ring-teal-300': oIdx === week.selected_index && !week.locked,
                                                             'bg-amber-50 ring-1 ring-amber-300': oIdx === week.selected_index && week.locked,
                                                             'hover:bg-gray-50': oIdx !== week.selected_index,
                                                         }">

                                                        <div class="flex items-start gap-1">
                                                            {{-- Radio dot --}}
                                                            <div class="mt-0.5 shrink-0">
                                                                <div class="w-3 h-3 rounded-full border-2 flex items-center justify-center"
                                                                     :class="oIdx === week.selected_index
                                                                        ? (week.locked ? 'border-amber-500 bg-amber-500' : 'border-teal-500 bg-teal-500')
                                                                        : 'border-gray-300'">
                                                                    <template x-if="oIdx === week.selected_index && week.locked">
                                                                        <svg class="w-2 h-2 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                                                        </svg>
                                                                    </template>
                                                                </div>
                                                            </div>

                                                            {{-- Camp info --}}
                                                            <div class="min-w-0 flex-1">
                                                                <div class="flex items-center gap-1">
                                                                    <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                                                          :class="categoryDot[opt.category] || 'bg-gray-400'"></span>
                                                                    <span class="text-[10px] font-semibold text-gray-900 leading-tight truncate"
                                                                          x-text="opt.camp_name"></span>
                                                                </div>
                                                                <div class="flex items-center justify-between mt-0.5">
                                                                    <span class="text-[10px] font-bold" x-text="formatPrice(opt.price_cents)"></span>
                                                                    <span class="text-[9px] font-medium px-1 py-px rounded-full"
                                                                          :class="{
                                                                              'bg-green-100 text-green-700': opt.availability_status === 'available',
                                                                              'bg-yellow-100 text-yellow-700': opt.availability_status === 'almost_full',
                                                                              'bg-red-100 text-red-700': opt.availability_status === 'waitlist'
                                                                          }"
                                                                          x-text="shortAvail(opt)"></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Hover tooltip --}}
                                                    <div x-show="showTip" x-cloak
                                                         x-transition:enter="transition ease-out duration-100"
                                                         x-transition:enter-start="opacity-0 scale-95"
                                                         x-transition:enter-end="opacity-100 scale-100"
                                                         x-transition:leave="transition ease-in duration-75"
                                                         x-transition:leave-start="opacity-100 scale-100"
                                                         x-transition:leave-end="opacity-0 scale-95"
                                                         class="absolute z-50 w-56 bg-white rounded-lg shadow-xl border border-gray-200 p-3 text-left pointer-events-none"
                                                         :class="wIdx >= 7 ? 'right-full mr-1 top-0' : 'left-full ml-1 top-0'">
                                                        {{-- Header --}}
                                                        <div class="flex items-center gap-1.5 mb-2">
                                                            <span class="w-2 h-2 rounded-full shrink-0"
                                                                  :class="categoryDot[opt.category] || 'bg-gray-400'"></span>
                                                            <span class="text-xs font-bold text-gray-900" x-text="opt.camp_name"></span>
                                                        </div>

                                                        {{-- Facility --}}
                                                        <div class="text-[11px] text-gray-700 font-medium" x-text="opt.facility_name"></div>
                                                        <div class="text-[10px] text-gray-500" x-text="opt.neighborhood + ', ' + opt.borough"></div>

                                                        {{-- Details grid --}}
                                                        <div class="grid grid-cols-2 gap-x-3 gap-y-1 mt-2 text-[10px]">
                                                            <div>
                                                                <span class="text-gray-400">Category</span>
                                                                <div class="font-medium text-gray-700" x-text="formatCategory(opt.category)"></div>
                                                            </div>
                                                            <div>
                                                                <span class="text-gray-400">Ages</span>
                                                                <div class="font-medium text-gray-700" x-text="opt.ages"></div>
                                                            </div>
                                                            <div>
                                                                <span class="text-gray-400">Schedule</span>
                                                                <div class="font-medium text-gray-700" x-text="formatSchedule(opt.schedule_type)"></div>
                                                            </div>
                                                            <div>
                                                                <span class="text-gray-400">Price</span>
                                                                <div class="font-bold text-gray-900" x-text="formatPrice(opt.price_cents) + '/wk'"></div>
                                                            </div>
                                                        </div>

                                                        {{-- Availability --}}
                                                        <div class="mt-2 flex items-center gap-2">
                                                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded-full"
                                                                  :class="{
                                                                      'bg-green-100 text-green-700': opt.availability_status === 'available',
                                                                      'bg-yellow-100 text-yellow-700': opt.availability_status === 'almost_full',
                                                                      'bg-red-100 text-red-700': opt.availability_status === 'waitlist'
                                                                  }"
                                                                  x-text="longAvail(opt)"></span>
                                                            <template x-if="opt.lunch_provided">
                                                                <span class="text-[10px] text-gray-500 flex items-center gap-0.5">
                                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                                    </svg>
                                                                    Lunch included
                                                                </span>
                                                            </template>
                                                        </div>

                                                        {{-- Reason --}}
                                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                                            <p class="text-[10px] text-gray-500 italic" x-text="opt.reason"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Lock / Sibling indicator row --}}
                                            <div class="flex items-center justify-between px-1 pt-0.5">
                                                <div class="flex items-center gap-1">
                                                    <template x-if="isSiblingOverlap(week, cIdx)">
                                                        <span class="text-[9px] bg-purple-100 text-purple-600 px-1 py-px rounded font-medium">Sibling</span>
                                                    </template>
                                                    <template x-if="selectedOpt(week)?.lunch_provided">
                                                        <span class="text-[9px] text-gray-400">+lunch</span>
                                                    </template>
                                                </div>
                                                <button @click.stop="toggleLock(cIdx, wIdx)"
                                                        class="text-[9px] font-medium px-1.5 py-0.5 rounded transition-colors"
                                                        :class="week.locked
                                                            ? 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                                                            : 'text-gray-400 hover:text-teal-600 hover:bg-teal-50'">
                                                    <span x-text="week.locked ? 'Locked' : 'Lock'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Empty --}}
                                    <template x-if="!week.blocked && (!week.options || week.options.length === 0)">
                                        <div class="min-h-[100px] flex flex-col items-center justify-center">
                                            <div class="text-[10px] text-gray-300">No match</div>
                                            <button @click="toggleBlock(cIdx, week.week_start)"
                                                    class="mt-1 text-[10px] text-gray-400 hover:text-red-500">Block</button>
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
        blockedWeeks: {},
        lockedCamps: {},

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
            const cols = 10;
            const rows = this.results?.children.length || 1;
            return `grid-template-columns: 90px repeat(${cols}, minmax(0, 1fr)); grid-template-rows: auto repeat(${rows}, 1fr);`;
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
                const res = await fetch('/api/recommend', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ prompt: this.prompt }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.error || 'Something went wrong');
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

            // Collect locked selections
            const locked = {};
            for (const child of this.results.children) {
                for (const [cIdx, c] of this.results.children.entries()) {
                    if (!locked[cIdx]) locked[cIdx] = {};
                    for (const week of c.weeks) {
                        if (week.locked && week.options && week.options[week.selected_index]) {
                            locked[cIdx][week.week_start] = week.options[week.selected_index];
                        }
                    }
                }
                break; // only need one pass
            }

            try {
                const res = await fetch('/api/recommend', {
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
                        locked_camps: locked,
                    }),
                });
                const data = await res.json();
                if (!res.ok || !data.success) throw new Error(data.error || 'Something went wrong');
                this.results = data.data;
            } catch (err) {
                this.error = err.message || 'Failed to re-plan.';
            } finally {
                this.loading = false;
            }
        },

        selectOption(cIdx, wIdx, oIdx) {
            const week = this.results.children[cIdx].weeks[wIdx];
            if (week.locked) return; // can't change locked
            week.selected_index = oIdx;
        },

        toggleBlock(cIdx, weekStart) {
            if (!this.blockedWeeks[cIdx]) this.blockedWeeks[cIdx] = [];
            const idx = this.blockedWeeks[cIdx].indexOf(weekStart);
            if (idx >= 0) {
                this.blockedWeeks[cIdx].splice(idx, 1);
            } else {
                this.blockedWeeks[cIdx].push(weekStart);
            }
            const week = this.results.children[cIdx].weeks.find(w => w.week_start === weekStart);
            if (week) {
                week.blocked = !week.blocked;
                if (week.blocked) week.locked = false;
            }
        },

        toggleLock(cIdx, wIdx) {
            const week = this.results.children[cIdx].weeks[wIdx];
            week.locked = !week.locked;
        },

        selectedOpt(week) {
            if (!week.options || week.options.length === 0) return null;
            return week.options[week.selected_index || 0];
        },

        calcTotal() {
            let total = 0;
            if (!this.results) return 0;
            for (const child of this.results.children) {
                for (const week of child.weeks) {
                    if (!week.blocked && week.options && week.options.length > 0) {
                        total += week.options[week.selected_index || 0].price_cents;
                    }
                }
            }
            return total;
        },

        hasLockedOrBlocked() {
            return this.countLocked() > 0 || this.countBlocked() > 0;
        },

        countLocked() {
            if (!this.results) return 0;
            let count = 0;
            for (const c of this.results.children) {
                for (const w of c.weeks) { if (w.locked) count++; }
            }
            return count;
        },

        countBlocked() {
            let count = 0;
            for (const k in this.blockedWeeks) count += this.blockedWeeks[k].length;
            return count;
        },

        clearConstraints() {
            this.blockedWeeks = {};
            if (this.results) {
                for (const c of this.results.children) {
                    for (const w of c.weeks) { w.blocked = false; w.locked = false; }
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

        formatCategory(cat) {
            if (!cat) return '';
            return cat.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        formatSchedule(type) {
            const map = { full_day: 'Full Day', half_day_am: 'Half Day (AM)', half_day_pm: 'Half Day (PM)' };
            return map[type] || type;
        },

        shortAvail(rec) {
            if (rec.availability_status === 'available') return rec.spots_remaining + ' spots';
            if (rec.availability_status === 'almost_full') return rec.spots_remaining + ' left!';
            return 'WL:' + rec.waitlist_count;
        },

        longAvail(rec) {
            if (rec.availability_status === 'available') return rec.spots_remaining + ' spots available';
            if (rec.availability_status === 'almost_full') return 'Almost full — ' + rec.spots_remaining + ' spots left';
            return 'Waitlist — ' + rec.waitlist_count + ' ahead of you';
        },

        isSiblingOverlap(week, cIdx) {
            if (!this.results?.sibling_overlaps) return false;
            const sel = this.selectedOpt(week);
            if (!sel) return false;
            return this.results.sibling_overlaps.some(o =>
                o.week_start === week.week_start && o.facility_name === sel.facility_name
            );
        },
    };
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>

</body>
</html>
