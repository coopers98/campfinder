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
                <svg class="w-6 h-6 text-sawyer-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V7l9-4 9 4v14M3 21h18M9 21V11h6v10"/>
                </svg>
                <span class="text-lg font-bold text-gray-900">Camp<span class="text-sawyer-500">Finder</span> AI</span>
            </div>
            <div class="flex items-center gap-4">
                <a href="/data" class="text-xs text-sawyer-500 hover:text-sawyer-700 font-medium">Sample Data</a>
                <span class="text-xs text-gray-400">NYC Summer 2026</span>
            </div>
        </div>
    </nav>

    {{-- Hero / Input (collapses when results shown) --}}
    <div x-show="!results" class="flex-1 flex items-start justify-center overflow-auto bg-gradient-to-b from-sawyer-50 via-sawyer-50/30 to-gray-50">
        <div class="w-full max-w-2xl mx-auto px-4 pt-12 pb-8 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-3">
                Find the Perfect <span class="text-sawyer-500">Summer Camp</span>
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
                    class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-sawyer-500 focus:border-transparent resize-none"
                    placeholder="I have a 6-year-old who loves art and a 9-year-old who's into soccer. We live in Park Slope and our budget is around $400/week per kid."
                    :disabled="loading"
                ></textarea>

                <div class="flex items-center justify-between mt-3">
                    <span class="text-xs text-gray-400" x-text="prompt.length + '/2000'"></span>
                    <button
                        @click="submitPrompt()"
                        :disabled="loading || !prompt.trim()"
                        class="bg-sawyer-500 hover:bg-sawyer-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold px-6 py-2 rounded-xl text-sm transition-colors"
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
                                class="text-xs bg-gray-50 hover:bg-sawyer-50 text-gray-600 hover:text-sawyer-600 px-3 py-1.5 rounded-lg border border-gray-200 hover:border-sawyer-200 transition-colors"
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
            <svg class="animate-spin w-5 h-5 text-sawyer-500" fill="none" viewBox="0 0 24 24">
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
                        <h2 class="text-lg font-bold text-gray-900">Summer Camp Plan</h2>
                        <div class="flex items-center gap-1">
                            <span class="text-sm text-gray-500">Total:</span>
                            <span class="text-lg font-bold text-gray-900" x-text="formatPrice(calcTotal())"></span>
                        </div>
                        <template x-if="getLocation()">
                            <div class="flex items-center gap-1 text-sm text-gray-500">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span x-text="getLocation()"></span>
                                <span class="text-gray-400">(distances from here)</span>
                            </div>
                        </template>
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
                                    class="text-xs bg-sawyer-500 hover:bg-sawyer-600 disabled:opacity-50 text-white font-semibold px-4 py-1.5 rounded-lg transition-colors">
                                Re-plan Unlocked
                            </button>
                        </template>
                        <button @click="clearConstraints()"
                                x-show="hasLockedOrBlocked()"
                                class="text-xs bg-white border border-gray-200 hover:border-gray-300 text-gray-600 px-3 py-1.5 rounded-lg transition-colors">
                            Clear All
                        </button>
                        <button @click="resetForm()"
                                class="text-xs border border-sawyer-300 text-sawyer-500 hover:bg-sawyer-50 font-semibold px-4 py-1.5 rounded-lg transition-colors">
                            Start Over
                        </button>
                    </div>
                </div>
            </div>

            {{-- Month navigation --}}
            <div class="bg-white border-b border-gray-200 px-4 py-2 shrink-0">
                <div class="flex items-center gap-3">
                    <button @click="prevMonth()"
                            :disabled="currentMonth === 0"
                            class="p-1 rounded hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <span class="text-base font-bold text-gray-900 min-w-[80px] text-center" x-text="monthNames[currentMonth]"></span>
                    <button @click="nextMonth()"
                            :disabled="currentMonth === 2"
                            class="p-1 rounded hover:bg-gray-100 disabled:opacity-30 disabled:cursor-not-allowed transition-colors">
                        <svg class="w-5 h-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                    <div class="flex gap-1 ml-2">
                        <template x-for="(m, mIdx) in monthNames" :key="m">
                            <button @click="currentMonth = mIdx"
                                    class="text-xs px-2.5 py-1 rounded-full font-medium transition-colors"
                                    :class="currentMonth === mIdx
                                        ? 'bg-sawyer-500 text-white'
                                        : 'bg-gray-100 text-gray-600 hover:bg-gray-200'">
                                <span x-text="m"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Grid --}}
            <div class="flex-1 overflow-auto min-h-0">
                <div class="grid min-h-full" :style="gridStyle()">
                    {{-- Header row --}}
                    <div class="sticky left-0 top-0 z-30 bg-gray-100 border-b border-r border-gray-200 px-3 py-3 flex items-end">
                        <span class="text-xs font-semibold text-gray-500">Children</span>
                    </div>
                    <template x-for="(weekStart, vIdx) in visibleWeeks()" :key="weekStart">
                        <div class="sticky top-0 z-20 bg-gray-100 border-b border-gray-200 px-2 py-3 text-center"
                             :class="vIdx < visibleWeeks().length - 1 ? 'border-r border-gray-100' : ''">
                            <div class="text-xs font-bold text-gray-700" x-text="'Wk ' + (globalWeekIndex(weekStart) + 1)"></div>
                            <div class="text-sm font-bold text-gray-900" x-text="shortWeekLabel(weekStart)"></div>
                        </div>
                    </template>

                    {{-- Child rows --}}
                    <template x-for="(child, cIdx) in results.children" :key="cIdx">
                        <div class="contents">
                            {{-- Child label with tooltip --}}
                            <div class="sticky left-0 z-10 bg-white border-b border-r border-gray-200 px-2 py-2 flex items-start gap-2 relative"
                                 x-data="{ showChildTip: false }"
                                 @mouseenter="showChildTip = true" @mouseleave="showChildTip = false">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0 mt-0.5"
                                     :class="childColors[cIdx % childColors.length]">
                                    <span x-text="child.name.charAt(0).toUpperCase()"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-bold text-gray-900 truncate" x-text="child.name"></div>
                                    <div class="text-xs text-gray-500" x-text="'Age ' + child.age"></div>
                                </div>

                                {{-- Child info tooltip --}}
                                <div x-show="showChildTip" x-cloak
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     class="absolute left-full ml-2 top-0 z-50 w-52 bg-white rounded-lg shadow-xl border border-gray-200 p-3 text-left pointer-events-none">
                                    <div class="text-xs font-bold text-gray-900 mb-2" x-text="child.name + ', Age ' + child.age"></div>
                                    <div class="space-y-1.5 text-[10px]">
                                        <div>
                                            <span class="text-gray-400">Interests</span>
                                            <div class="flex flex-wrap gap-1 mt-0.5">
                                                <template x-for="cat in child.categories" :key="cat">
                                                    <span class="px-1.5 py-0.5 rounded-full font-medium"
                                                          :class="categoryBadge[cat] || 'bg-gray-100 text-gray-700'"
                                                          x-text="formatCategory(cat)"></span>
                                                </template>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Location</span>
                                            <div class="font-medium text-gray-700" x-text="child.borough || 'Any borough'"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Budget</span>
                                            <div class="font-medium text-gray-700" x-text="formatPrice(child.budget_cents) + '/week'"></div>
                                        </div>
                                        <div>
                                            <span class="text-gray-400">Schedule</span>
                                            <div class="font-medium text-gray-700" x-text="child.schedule_preference === 'any' ? 'Any schedule' : formatSchedule(child.schedule_preference)"></div>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                        <p class="text-[10px] text-gray-500 italic" x-text="child.summary"></p>
                                    </div>
                                </div>
                            </div>

                            {{-- Week cells (visible month only) --}}
                            <template x-for="(week, vIdx) in visibleChildWeeks(child)" :key="week.week_start">
                                <div class="border-b border-r border-gray-100 p-1.5 relative"
                                     :class="{
                                         'bg-gray-50': week.blocked,
                                         'bg-white': !week.blocked,
                                     }">

                                    {{-- Blocked --}}
                                    <template x-if="week.blocked">
                                        <div class="h-full min-h-[100px] flex flex-col items-center justify-center">
                                            <div class="text-sm text-gray-400 font-medium">Off</div>
                                            <button @click="toggleBlock(cIdx, week.week_start)"
                                                    class="mt-1 text-xs text-sawyer-500 hover:text-sawyer-700">Unblock</button>
                                        </div>
                                    </template>

                                    {{-- Options list --}}
                                    <template x-if="!week.blocked && week.options && week.options.length > 0">
                                        <div class="space-y-0.5 min-h-[100px]">

                                            <template x-for="(opt, oIdx) in week.options" :key="opt.camp_id">
                                                <div class="relative"
                                                     x-data="{ showTip: false }"
                                                     @mouseenter="showTip = true" @mouseleave="showTip = false">

                                                    {{-- Divider between interest matches and other options --}}
                                                    <template x-if="showAlsoDivider(child, week.options, oIdx)">
                                                        <div class="flex items-center gap-1.5 py-0.5 mb-0.5">
                                                            <div class="flex-1 border-t border-dashed border-gray-300"></div>
                                                            <span class="text-[10px] text-gray-400 uppercase tracking-wide shrink-0">Also available</span>
                                                            <div class="flex-1 border-t border-dashed border-gray-300"></div>
                                                        </div>
                                                    </template>
                                                    <div @click="selectOption(cIdx, globalWeekIndex(week.week_start), oIdx)"
                                                         class="rounded px-1.5 py-1 cursor-pointer transition-all text-left"
                                                         :class="optionRowClass(week, cIdx, oIdx, opt)">

                                                        <div class="flex items-start gap-1">
                                                            {{-- Radio dot --}}
                                                            <div class="mt-0.5 shrink-0">
                                                                <div class="w-3.5 h-3.5 rounded-full border-2 flex items-center justify-center"
                                                                     :class="oIdx === week.selected_index
                                                                        ? (week.locked ? 'border-amber-500 bg-amber-500' : 'border-sawyer-500 bg-sawyer-500')
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
                                                                    <span class="text-sm shrink-0 leading-none" x-text="categoryEmoji[opt.category] || '☀️'"></span>
                                                                    <span class="text-xs font-semibold text-gray-900 leading-tight truncate"
                                                                          x-text="opt.camp_name"></span>
                                                                    <template x-if="siblingMatchType(week, cIdx, opt) === 'camp'">
                                                                        <span class="shrink-0 text-[10px] px-1 rounded-sm font-bold border"
                                                                              :class="siblingBadgeBg[siblingMatchChildIdx(week, cIdx, opt) % 4] + ' ' + siblingBadgeText[siblingMatchChildIdx(week, cIdx, opt) % 4] + ' ' + siblingBadgeBorder[siblingMatchChildIdx(week, cIdx, opt) % 4]"
                                                                              :title="'Same camp as ' + (results.children[siblingMatchChildIdx(week, cIdx, opt)]?.name || 'sibling')">SAME</span>
                                                                    </template>
                                                                    <template x-if="siblingMatchType(week, cIdx, opt) === 'facility'">
                                                                        <span class="shrink-0 text-[10px] px-1 rounded-sm font-bold border"
                                                                              :class="siblingBadgeBg[siblingMatchChildIdx(week, cIdx, opt) % 4] + ' ' + siblingBadgeText[siblingMatchChildIdx(week, cIdx, opt) % 4] + ' ' + siblingBadgeBorder[siblingMatchChildIdx(week, cIdx, opt) % 4]"
                                                                              :title="'Same facility as ' + (results.children[siblingMatchChildIdx(week, cIdx, opt)]?.name || 'sibling')">FAC</span>
                                                                    </template>
                                                                </div>
                                                                <div class="flex items-center gap-1.5 mt-0.5">
                                                                    <span class="text-xs font-bold" x-text="formatPrice(opt.price_cents)"></span>
                                                                    <template x-if="opt.lunch_provided">
                                                                        <span class="text-[10px] text-gray-500" title="Lunch included">🥪 Lunch</span>
                                                                    </template>
                                                                    <template x-if="opt.distance_miles !== null">
                                                                        <span class="text-[10px] text-gray-400" x-text="opt.distance_miles + 'mi'"></span>
                                                                    </template>
                                                                    <span class="ml-auto text-[10px] font-medium px-1.5 py-0.5 rounded-full"
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
                                                         :class="vIdx >= visibleWeeks().length - 2 ? 'right-full mr-1 top-0' : 'left-full ml-1 top-0'">
                                                        {{-- Header --}}
                                                        <div class="flex items-center gap-1.5 mb-2">
                                                            <span class="text-sm" x-text="categoryEmoji[opt.category] || '☀️'"></span>
                                                            <span class="text-xs font-bold text-gray-900" x-text="opt.camp_name"></span>
                                                        </div>

                                                        {{-- Facility --}}
                                                        <div class="text-[11px] text-gray-700 font-medium" x-text="opt.facility_name"></div>
                                                        <div class="text-[10px] text-gray-500">
                                                            <span x-text="opt.neighborhood + ', ' + opt.borough"></span>
                                                            <template x-if="opt.distance_miles !== null">
                                                                <span class="ml-1 text-sawyer-500 font-medium" x-text="'(' + opt.distance_miles + ' mi)'"></span>
                                                            </template>
                                                        </div>

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
                                                            <template x-if="opt.distance_miles !== null">
                                                                <div>
                                                                    <span class="text-gray-400">Distance</span>
                                                                    <div class="font-medium text-sawyer-500" x-text="opt.distance_miles + ' miles'"></div>
                                                                </div>
                                                            </template>
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

                                                        {{-- Sibling match callout --}}
                                                        <template x-if="siblingMatchType(week, cIdx, opt)">
                                                            <div class="mt-2 flex items-center gap-1.5 bg-purple-50 rounded px-2 py-1">
                                                                <svg class="w-3 h-3 text-purple-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                                </svg>
                                                                <span class="text-[10px] text-purple-700 font-medium"
                                                                      x-text="siblingMatchType(week, cIdx, opt) === 'camp'
                                                                          ? 'Same camp available for sibling!'
                                                                          : 'Same facility available for sibling'"></span>
                                                            </div>
                                                        </template>

                                                        {{-- Reason --}}
                                                        <div class="mt-2 pt-2 border-t border-gray-100">
                                                            <p class="text-[10px] text-gray-500 italic" x-text="opt.reason"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Lock + Block row --}}
                                            <div class="flex items-center justify-between px-1 pt-1.5 border-t border-gray-100 mt-1">
                                                <button @click.stop="toggleBlock(cIdx, week.week_start)"
                                                        class="text-xs font-medium px-2 py-0.5 rounded text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors">
                                                    Block
                                                </button>
                                                <button @click.stop="toggleLock(cIdx, globalWeekIndex(week.week_start))"
                                                        class="text-xs font-medium px-2 py-0.5 rounded transition-colors"
                                                        :class="week.locked
                                                            ? 'bg-amber-100 text-amber-700 hover:bg-amber-200'
                                                            : 'text-gray-400 hover:text-sawyer-500 hover:bg-sawyer-50'">
                                                    <span x-text="week.locked ? 'Locked' : 'Lock'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Empty --}}
                                    <template x-if="!week.blocked && (!week.options || week.options.length === 0)">
                                        <div class="min-h-[100px] flex flex-col items-center justify-center">
                                            <div class="text-sm text-gray-300">No match</div>
                                            <button @click="toggleBlock(cIdx, week.week_start)"
                                                    class="mt-1 text-xs text-gray-400 hover:text-red-500">Block</button>
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
        currentMonth: 0,

        weekStarts: [
            '2026-06-15', '2026-06-22', '2026-06-29',
            '2026-07-06', '2026-07-13', '2026-07-20', '2026-07-27',
            '2026-08-03', '2026-08-10', '2026-08-17',
        ],

        monthNames: ['June', 'July', 'August'],

        monthWeekRanges: [
            [0, 3],  // June: weeks 0-2 (3 weeks)
            [3, 7],  // July: weeks 3-6 (4 weeks)
            [7, 10], // August: weeks 7-9 (3 weeks)
        ],

        examples: [
            "I have a 6-year-old who loves art and a 9-year-old who's into soccer. We live in Park Slope and our budget is around $400/week per kid.",
            "Looking for STEM camps for my 10-year-old in Manhattan. Full day preferred, budget up to $600/week.",
            "Three kids: ages 4, 7, and 11. We live in Astoria. Would love them at the same place when possible!",
            "My 8-year-old daughter loves theater and dance. Brooklyn area, up to $500/week.",
        ],

        childColors: ['bg-sawyer-500', 'bg-indigo-600', 'bg-rose-600', 'bg-amber-600'],
        siblingBadgeBg: ['bg-sawyer-100', 'bg-indigo-100', 'bg-rose-100', 'bg-amber-100'],
        siblingBadgeText: ['text-sawyer-700', 'text-indigo-700', 'text-rose-700', 'text-amber-700'],
        siblingBadgeBorder: ['border-sawyer-300', 'border-indigo-300', 'border-rose-300', 'border-amber-300'],

        categoryEmoji: {
            sports: '⚽',
            arts: '🎨',
            performing_arts: '🎭',
            stem: '🔬',
            nature: '🌿',
            academic: '📚',
            martial_arts: '🥋',
            equestrian: '🐴',
            pets: '🐾',
            general: '☀️',
        },

        categoryDot: {
            sports: 'bg-blue-500',
            arts: 'bg-pink-500',
            performing_arts: 'bg-purple-500',
            stem: 'bg-orange-500',
            nature: 'bg-green-500',
            academic: 'bg-indigo-500',
            martial_arts: 'bg-red-500',
            equestrian: 'bg-amber-600',
            pets: 'bg-lime-500',
            general: 'bg-gray-400',
        },

        categoryBadge: {
            sports: 'bg-blue-100 text-blue-700',
            arts: 'bg-pink-100 text-pink-700',
            performing_arts: 'bg-purple-100 text-purple-700',
            stem: 'bg-orange-100 text-orange-700',
            nature: 'bg-green-100 text-green-700',
            academic: 'bg-indigo-100 text-indigo-700',
            martial_arts: 'bg-red-100 text-red-700',
            equestrian: 'bg-amber-100 text-amber-700',
            pets: 'bg-lime-100 text-lime-700',
            general: 'bg-gray-100 text-gray-700',
        },

        gridStyle() {
            const cols = this.visibleWeeks().length;
            const rows = this.results?.children.length || 1;
            return `grid-template-columns: 110px repeat(${cols}, minmax(0, 1fr)); grid-template-rows: auto repeat(${rows}, 1fr);`;
        },

        visibleWeeks() {
            const [start, end] = this.monthWeekRanges[this.currentMonth];
            return this.weekStarts.slice(start, end);
        },

        visibleChildWeeks(child) {
            const [start, end] = this.monthWeekRanges[this.currentMonth];
            return child.weeks.slice(start, end);
        },

        globalWeekIndex(weekStart) {
            return this.weekStarts.indexOf(weekStart);
        },

        getLocation() {
            // Use resolved borough from plan data, fall back to parsed criteria
            if (this.results?.children?.[0]?.borough) return this.results.children[0].borough;
            if (this.parsedCriteria?.borough) return this.parsedCriteria.borough;
            return null;
        },

        prevMonth() {
            if (this.currentMonth > 0) this.currentMonth--;
        },

        nextMonth() {
            if (this.currentMonth < 2) this.currentMonth++;
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

            // Collect locked selections and exclude IDs for unlocked weeks
            const locked = {};
            const excludeCamps = {};
            for (const [cIdx, c] of this.results.children.entries()) {
                locked[cIdx] = {};
                excludeCamps[cIdx] = {};
                for (const week of c.weeks) {
                    if (week.locked && week.options && week.options[week.selected_index]) {
                        locked[cIdx][week.week_start] = week.options[week.selected_index];
                    } else if (!week.blocked && week.options) {
                        // Exclude current options so re-plan shows fresh camps
                        excludeCamps[cIdx][week.week_start] = week.options.map(o => o.camp_id);
                    }
                }
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
                        exclude_camps: excludeCamps,
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

        isInterestMatch(child, opt) {
            if (!opt) return false;
            // Use backend flag if available, fall back to client-side check
            if (typeof opt.interest_match !== 'undefined') return opt.interest_match;
            return child.categories.includes(opt.category) || opt.category === 'general';
        },

        showAlsoDivider(child, options, oIdx) {
            if (oIdx === 0) return false;
            // Show divider at the boundary where interest matches end
            const prev = options[oIdx - 1];
            const curr = options[oIdx];
            return this.isInterestMatch(child, prev) && !this.isInterestMatch(child, curr);
        },

        siblingMatchType(week, cIdx, opt) {
            if (!this.results || this.results.children.length < 2) return null;
            let hasFacility = false;
            let matchIdx = -1;
            for (let i = 0; i < this.results.children.length; i++) {
                if (i === cIdx) continue;
                const otherWeek = this.results.children[i].weeks.find(w => w.week_start === week.week_start);
                if (!otherWeek || otherWeek.blocked) continue;
                const otherOpts = otherWeek.options || [];
                if (otherOpts.some(o => o.camp_name === opt.camp_name && o.facility_id === opt.facility_id)) return 'camp';
                if (otherOpts.some(o => o.facility_id === opt.facility_id)) { hasFacility = true; matchIdx = i; }
            }
            return hasFacility ? 'facility' : null;
        },

        // Returns the index of the sibling child that shares a facility with this option
        siblingMatchChildIdx(week, cIdx, opt) {
            if (!this.results || this.results.children.length < 2) return -1;
            for (let i = 0; i < this.results.children.length; i++) {
                if (i === cIdx) continue;
                const otherWeek = this.results.children[i].weeks.find(w => w.week_start === week.week_start);
                if (!otherWeek || otherWeek.blocked) continue;
                const otherOpts = otherWeek.options || [];
                if (otherOpts.some(o => o.facility_id === opt.facility_id)) return i;
            }
            return -1;
        },

        optionRowClass(week, cIdx, oIdx, opt) {
            const selected = oIdx === week.selected_index;
            const locked = week.locked;
            const sibType = this.siblingMatchType(week, cIdx, opt);

            if (selected && locked) return 'bg-amber-50 ring-1 ring-amber-300';
            if (selected && sibType === 'camp') return 'bg-purple-50 ring-1 ring-purple-400';
            if (selected && sibType === 'facility') return 'bg-purple-50 ring-1 ring-purple-300';
            if (selected) return 'bg-sawyer-50 ring-1 ring-sawyer-300';
            if (sibType === 'camp') return 'bg-purple-50/50 border border-dashed border-purple-200 hover:bg-purple-50';
            if (sibType === 'facility') return 'bg-purple-50/30 border border-dashed border-purple-100 hover:bg-purple-50';
            return 'hover:bg-gray-50';
        },
    };
}
</script>

<style>
    [x-cloak] { display: none !important; }
</style>

</body>
</html>
