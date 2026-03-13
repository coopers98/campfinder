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
<body class="bg-white text-gray-800 font-sans antialiased">

<div x-data="campFinder()" class="min-h-screen">
    {{-- Nav --}}
    <nav class="bg-white border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <svg class="w-8 h-8 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V7l9-4 9 4v14M3 21h18M9 21V11h6v10"/>
                </svg>
                <span class="text-xl font-bold text-gray-900">Camp<span class="text-teal-600">Finder</span> AI</span>
            </div>
            <span class="text-sm text-gray-400">NYC Summer 2026</span>
        </div>
    </nav>

    {{-- Hero --}}
    <div class="bg-gradient-to-b from-teal-50 via-teal-50/30 to-white">
        <div class="max-w-4xl mx-auto px-4 pt-16 pb-12 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-4">
                Find the Perfect <span class="text-teal-600">Summer Camp</span> for Your Kids
            </h1>
            <p class="text-lg text-gray-600 mb-10 max-w-2xl mx-auto">
                Tell us about your children and we'll plan their entire summer with personalized camp recommendations across NYC.
            </p>

            {{-- Input Form --}}
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sm:p-8 text-left max-w-2xl mx-auto">
                <label for="prompt" class="block text-sm font-semibold text-gray-700 mb-2">
                    Describe your kids and what you're looking for
                </label>
                <textarea
                    id="prompt"
                    x-model="prompt"
                    @keydown.meta.enter="submitPrompt()"
                    rows="4"
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent resize-none transition"
                    placeholder="I have a 6-year-old who loves art and a 9-year-old who's into soccer. We live in Park Slope and our budget is around $400/week per kid. They'd love to be at the same place if possible!"
                    :disabled="loading"
                ></textarea>

                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-gray-400" x-text="prompt.length + '/2000'"></span>
                    <button
                        @click="submitPrompt()"
                        :disabled="loading || !prompt.trim()"
                        class="bg-teal-600 hover:bg-teal-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold px-6 py-2.5 rounded-xl transition-colors"
                    >
                        <span x-show="!loading">Find Camps</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Planning summer...
                        </span>
                    </button>
                </div>

                {{-- Example prompts --}}
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-400 mb-2">Try an example:</p>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="example in examples" :key="example">
                            <button
                                @click="prompt = example"
                                class="text-xs bg-gray-50 hover:bg-teal-50 text-gray-600 hover:text-teal-700 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-teal-200 transition-colors"
                                x-text="example.substring(0, 60) + '...'"
                                :disabled="loading"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Error --}}
    <div x-show="error" x-cloak class="max-w-4xl mx-auto px-4 mt-6">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center justify-between">
            <span x-text="error"></span>
            <button @click="error = null" class="text-red-400 hover:text-red-600">&times;</button>
        </div>
    </div>

    {{-- Results --}}
    <div x-show="results" x-cloak class="pb-20">
        {{-- Summary bar --}}
        <div class="bg-teal-50 border-y border-teal-100 py-4 mt-8">
            <div class="max-w-7xl mx-auto px-4 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Summer Camp Plan</h2>
                    <p class="text-sm text-gray-600" x-text="results?.notes"></p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <span class="text-xs text-gray-500 block">Estimated Total</span>
                        <span class="text-lg font-bold text-teal-700" x-text="formatPrice(results?.total_estimated_cost_cents)"></span>
                    </div>
                    <button @click="resetForm()" class="text-sm bg-white border border-gray-200 hover:border-teal-300 text-gray-600 px-4 py-2 rounded-lg transition-colors">
                        Try Again
                    </button>
                </div>
            </div>
        </div>

        {{-- Sibling overlaps --}}
        <template x-if="results?.sibling_overlaps?.length > 0">
            <div class="max-w-7xl mx-auto px-4 mt-4">
                <div class="bg-purple-50 border border-purple-200 rounded-xl px-4 py-3 flex items-start gap-2">
                    <svg class="w-5 h-5 text-purple-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-purple-800">Sibling Overlap Weeks</p>
                        <template x-for="overlap in results.sibling_overlaps" :key="overlap.week_start">
                            <p class="text-sm text-purple-700">
                                <span x-text="overlap.children_names.join(' & ')"></span> at
                                <span class="font-medium" x-text="overlap.facility_name"></span>
                                (<span x-text="formatWeekLabel(overlap.week_start)"></span>)
                            </p>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        {{-- Swim Lanes --}}
        <template x-for="(child, childIdx) in results?.children" :key="childIdx">
            <div class="mt-6">
                {{-- Child header --}}
                <div class="max-w-7xl mx-auto px-4 mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                             :class="childColors[childIdx % childColors.length]">
                            <span x-text="child.name.charAt(0).toUpperCase()"></span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900" x-text="child.name + ', age ' + child.age"></h3>
                            <p class="text-sm text-gray-500" x-text="child.summary"></p>
                        </div>
                    </div>
                </div>

                {{-- Scrollable timeline --}}
                <div class="overflow-x-auto pb-4">
                    <div class="flex gap-3 px-4 min-w-max max-w-7xl mx-auto">
                        <template x-for="(week, weekIdx) in child.weeks" :key="weekIdx">
                            <div class="w-64 shrink-0">
                                {{-- Week header --}}
                                <div class="text-xs font-semibold text-gray-500 mb-2 px-1" x-text="week.week_label"></div>

                                {{-- Camp card or empty --}}
                                <template x-if="week.primary_recommendation">
                                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition-shadow p-4"
                                         :class="isSiblingOverlap(week, child, childIdx) ? 'ring-2 ring-purple-300 border-purple-200' : ''">
                                        {{-- Category badge --}}
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                                  :class="categoryColors[week.primary_recommendation.category] || 'bg-gray-100 text-gray-700'"
                                                  x-text="formatCategory(week.primary_recommendation.category)"></span>
                                            <template x-if="isSiblingOverlap(week, child, childIdx)">
                                                <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-medium">Same Facility</span>
                                            </template>
                                        </div>

                                        {{-- Camp name --}}
                                        <h4 class="font-semibold text-gray-900 text-sm leading-tight" x-text="week.primary_recommendation.camp_name"></h4>
                                        <p class="text-xs text-gray-500 mt-0.5" x-text="week.primary_recommendation.facility_name"></p>

                                        {{-- Details --}}
                                        <div class="mt-3 space-y-1.5">
                                            <div class="flex items-center justify-between">
                                                <span class="text-xs text-gray-500" x-text="formatSchedule(week.primary_recommendation.schedule_type)"></span>
                                                <span class="text-sm font-semibold text-gray-900" x-text="formatPrice(week.primary_recommendation.price_cents)"></span>
                                            </div>
                                            {{-- Availability --}}
                                            <div>
                                                <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                                      :class="{
                                                          'bg-green-100 text-green-700': week.primary_recommendation.availability_status === 'available',
                                                          'bg-yellow-100 text-yellow-700': week.primary_recommendation.availability_status === 'almost_full',
                                                          'bg-red-100 text-red-700': week.primary_recommendation.availability_status === 'waitlist'
                                                      }">
                                                    <span x-text="availabilityLabel(week.primary_recommendation)"></span>
                                                </span>
                                            </div>
                                            {{-- Lunch --}}
                                            <template x-if="week.primary_recommendation.lunch_provided">
                                                <div class="flex items-center gap-1 text-xs text-gray-500">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                                                    </svg>
                                                    Lunch provided
                                                </div>
                                            </template>
                                        </div>

                                        {{-- Reason --}}
                                        <p class="mt-3 text-xs text-gray-400 italic leading-snug" x-text="week.primary_recommendation.reason"></p>

                                        {{-- Alternative toggle --}}
                                        <template x-if="week.alternative">
                                            <div class="mt-3 pt-3 border-t border-gray-100">
                                                <button @click="week._showAlt = !week._showAlt" class="text-xs text-teal-600 hover:text-teal-800 font-medium">
                                                    <span x-text="week._showAlt ? 'Hide alternative' : 'See alternative'"></span>
                                                </button>
                                                <div x-show="week._showAlt" x-cloak class="mt-2 bg-gray-50 rounded-lg p-3">
                                                    <span class="text-xs font-medium px-2 py-0.5 rounded-full"
                                                          :class="categoryColors[week.alternative.category] || 'bg-gray-100 text-gray-700'"
                                                          x-text="formatCategory(week.alternative.category)"></span>
                                                    <h5 class="font-medium text-gray-800 text-xs mt-1" x-text="week.alternative.camp_name"></h5>
                                                    <p class="text-xs text-gray-500" x-text="week.alternative.facility_name"></p>
                                                    <div class="flex items-center justify-between mt-1">
                                                        <span class="text-xs text-gray-500" x-text="formatSchedule(week.alternative.schedule_type)"></span>
                                                        <span class="text-xs font-semibold" x-text="formatPrice(week.alternative.price_cents)"></span>
                                                    </div>
                                                    <p class="text-xs text-gray-400 italic mt-1" x-text="week.alternative.reason"></p>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>

                                {{-- Empty week --}}
                                <template x-if="!week.primary_recommendation">
                                    <div class="border-2 border-dashed border-gray-200 rounded-xl p-4 h-32 flex items-center justify-center">
                                        <span class="text-sm text-gray-400">Open week</span>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
function campFinder() {
    return {
        prompt: '',
        loading: false,
        results: null,
        error: null,

        examples: [
            "I have a 6-year-old who loves art and a 9-year-old who's into soccer. We live in Park Slope and our budget is around $400/week per kid. They'd love to be at the same place if possible!",
            "Looking for STEM camps for my 10-year-old in Manhattan. Full day preferred, budget up to $600/week.",
            "Three kids: ages 4, 7, and 11. We live in Astoria. Would love them at the same place when possible! Budget is flexible.",
            "My 8-year-old daughter loves theater and dance. She's shy so smaller camps preferred. Brooklyn area, up to $500/week.",
        ],

        childColors: ['bg-teal-600', 'bg-indigo-600', 'bg-rose-600', 'bg-amber-600'],

        categoryColors: {
            sports: 'bg-blue-100 text-blue-700',
            arts: 'bg-pink-100 text-pink-700',
            performing_arts: 'bg-purple-100 text-purple-700',
            stem: 'bg-orange-100 text-orange-700',
            nature: 'bg-green-100 text-green-700',
            academic: 'bg-indigo-100 text-indigo-700',
            general: 'bg-gray-100 text-gray-700',
        },

        async submitPrompt() {
            if (!this.prompt.trim() || this.loading) return;

            this.loading = true;
            this.error = null;
            this.results = null;

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

                // Initialize _showAlt toggles
                data.data.children.forEach(child => {
                    child.weeks.forEach(week => {
                        week._showAlt = false;
                    });
                });

                this.results = data.data;

                // Scroll to results
                this.$nextTick(() => {
                    document.querySelector('[x-show="results"]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            } catch (err) {
                this.error = err.message || 'Failed to get recommendations. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        resetForm() {
            this.results = null;
            this.error = null;
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },

        formatPrice(cents) {
            if (!cents && cents !== 0) return '';
            return '$' + (cents / 100).toLocaleString('en-US', { maximumFractionDigits: 0 });
        },

        formatCategory(cat) {
            if (!cat) return '';
            return cat.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        },

        formatSchedule(type) {
            const map = { full_day: 'Full Day', half_day_am: 'Half Day (AM)', half_day_pm: 'Half Day (PM)' };
            return map[type] || type;
        },

        formatWeekLabel(weekStart) {
            if (!weekStart) return '';
            const d = new Date(weekStart + 'T00:00:00');
            return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        availabilityLabel(rec) {
            if (rec.availability_status === 'available') return rec.spots_remaining + ' spots left';
            if (rec.availability_status === 'almost_full') return 'Almost full! ' + rec.spots_remaining + ' left';
            return 'Waitlist (' + rec.waitlist_count + ' ahead)';
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
</style>

</body>
</html>
