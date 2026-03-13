<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CampFinder AI — Sample Data</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased">

<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 h-12 flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="/" class="flex items-center gap-2">
                <svg class="w-6 h-6 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 21V7l9-4 9 4v14M3 21h18M9 21V11h6v10"/>
                </svg>
                <span class="text-lg font-bold text-gray-900">Camp<span class="text-teal-600">Finder</span> AI</span>
            </a>
            <span class="text-sm text-gray-400">|</span>
            <span class="text-sm font-semibold text-gray-700">Sample Data Explorer</span>
        </div>
        <a href="/" class="text-sm text-teal-600 hover:text-teal-800 font-medium">Back to Finder</a>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-6">
    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-teal-600">{{ $stats['facilities'] }}</div>
            <div class="text-xs text-gray-500">Facilities</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-teal-600">{{ $stats['camps'] }}</div>
            <div class="text-xs text-gray-500">Camp Sessions</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-teal-600">{{ count($stats['boroughs']) }}</div>
            <div class="text-xs text-gray-500">Boroughs</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <div class="text-2xl font-bold text-teal-600">{{ count($stats['categories']) }}</div>
            <div class="text-xs text-gray-500">Categories</div>
        </div>
    </div>

    {{-- Facilities summary --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <h2 class="text-sm font-bold text-gray-900 mb-3">Facilities by Borough</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
            @foreach($facilities as $f)
                <a href="{{ url('/data?facility=' . $f->id) }}"
                   class="flex items-center justify-between px-3 py-2 rounded-lg hover:bg-teal-50 border border-gray-100 transition-colors {{ request('facility') == $f->id ? 'bg-teal-50 border-teal-200' : '' }}">
                    <div>
                        <div class="text-xs font-semibold text-gray-900">{{ $f->name }}</div>
                        <div class="text-[10px] text-gray-500">{{ $f->borough }} &middot; {{ $f->neighborhood }}</div>
                    </div>
                    <div class="text-xs text-gray-400">{{ $f->camps_count }} camps</div>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-4">
        <form method="GET" action="{{ url('/data') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 mb-1">Borough</label>
                <select name="borough" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                    <option value="">All</option>
                    @foreach($stats['boroughs'] as $b)
                        <option value="{{ $b }}" {{ request('borough') === $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 mb-1">Category</label>
                <select name="category" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                    <option value="">All</option>
                    @foreach($stats['categories'] as $c)
                        <option value="{{ $c }}" {{ request('category') === $c ? 'selected' : '' }}>{{ str_replace('_', ' ', ucfirst($c)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 mb-1">Week</label>
                <select name="week" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                    <option value="">All</option>
                    @foreach(['2026-06-15','2026-06-22','2026-06-29','2026-07-06','2026-07-13','2026-07-20','2026-07-27','2026-08-03','2026-08-10','2026-08-17'] as $i => $w)
                        <option value="{{ $w }}" {{ request('week') === $w ? 'selected' : '' }}>Wk {{ $i + 1 }}: {{ \Carbon\Carbon::parse($w)->format('M j') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-[10px] font-semibold text-gray-500 mb-1">Child Age</label>
                <select name="age" class="text-xs border border-gray-200 rounded-lg px-2 py-1.5">
                    <option value="">Any</option>
                    @for($a = 3; $a <= 14; $a++)
                        <option value="{{ $a }}" {{ request('age') == $a ? 'selected' : '' }}>{{ $a }} yrs</option>
                    @endfor
                </select>
            </div>
            @if(request('facility'))
                <input type="hidden" name="facility" value="{{ request('facility') }}">
            @endif
            <button type="submit" class="text-xs bg-teal-600 hover:bg-teal-700 text-white font-semibold px-4 py-1.5 rounded-lg">Filter</button>
            <a href="{{ url('/data') }}" class="text-xs text-gray-500 hover:text-gray-700">Clear</a>
        </form>
    </div>

    {{-- Camps table --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Camp</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Facility</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Borough</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Category</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Ages</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Week</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Schedule</th>
                        <th class="text-right px-3 py-2 font-semibold text-gray-700">Price</th>
                        <th class="text-left px-3 py-2 font-semibold text-gray-700">Availability</th>
                        <th class="text-center px-3 py-2 font-semibold text-gray-700">Lunch</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($camps as $camp)
                        <tr class="border-b border-gray-50 hover:bg-gray-50">
                            <td class="px-3 py-2 font-medium text-gray-900">{{ $camp->name }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $camp->facility->name }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $camp->facility->borough }}</td>
                            <td class="px-3 py-2">
                                @php
                                    $catColors = [
                                        'sports' => 'bg-blue-100 text-blue-700',
                                        'arts' => 'bg-pink-100 text-pink-700',
                                        'performing_arts' => 'bg-purple-100 text-purple-700',
                                        'stem' => 'bg-orange-100 text-orange-700',
                                        'nature' => 'bg-green-100 text-green-700',
                                        'academic' => 'bg-indigo-100 text-indigo-700',
                                        'general' => 'bg-gray-100 text-gray-700',
                                    ];
                                @endphp
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $catColors[$camp->category] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ str_replace('_', ' ', ucfirst($camp->category)) }}
                                </span>
                            </td>
                            <td class="px-3 py-2 text-gray-600">{{ $camp->age_min }}-{{ $camp->age_max }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ $camp->week_start->format('M j') }}</td>
                            <td class="px-3 py-2 text-gray-600">{{ str_replace('_', ' ', ucfirst($camp->schedule_type)) }}</td>
                            <td class="px-3 py-2 text-right font-medium text-gray-900">${{ number_format($camp->price_cents / 100) }}</td>
                            <td class="px-3 py-2">
                                @php $avail = $camp->availability_status; @endphp
                                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium
                                    {{ $avail === 'available' ? 'bg-green-100 text-green-700' : ($avail === 'almost_full' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                    @if($avail === 'available')
                                        {{ $camp->spots_remaining }} spots
                                    @elseif($avail === 'almost_full')
                                        {{ $camp->spots_remaining }} left
                                    @else
                                        WL: {{ $camp->waitlist_count }}
                                    @endif
                                </span>
                            </td>
                            <td class="px-3 py-2 text-center">
                                @if($camp->lunch_provided)
                                    <span class="text-green-600">Yes</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-3 py-8 text-center text-gray-400">No camps match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($camps->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">
                {{ $camps->links('pagination::tailwind') }}
            </div>
        @endif
    </div>
</div>

</body>
</html>
