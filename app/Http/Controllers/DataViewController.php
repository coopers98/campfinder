<?php

namespace App\Http\Controllers;

use App\Models\Camp;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataViewController extends Controller
{
    public function __invoke(Request $request): View
    {
        $facilities = Facility::withCount('camps')
            ->orderBy('borough')
            ->orderBy('name')
            ->get();

        $query = Camp::with('facility')->orderBy('facility_id')->orderBy('week_start')->orderBy('category');

        if ($request->filled('facility')) {
            $query->where('facility_id', $request->input('facility'));
        }
        if ($request->filled('borough')) {
            $query->inBorough($request->input('borough'));
        }
        if ($request->filled('category')) {
            $query->inCategory($request->input('category'));
        }
        if ($request->filled('week')) {
            $query->inWeek($request->input('week'));
        }
        if ($request->filled('age')) {
            $query->forAge((int) $request->input('age'));
        }

        $camps = $query->paginate(50)->withQueryString();

        $stats = [
            'facilities' => Facility::count(),
            'camps' => Camp::count(),
            'boroughs' => Facility::distinct('borough')->pluck('borough')->sort()->values(),
            'categories' => Camp::distinct('category')->pluck('category')->sort()->values(),
        ];

        return view('data', compact('facilities', 'camps', 'stats'));
    }
}
