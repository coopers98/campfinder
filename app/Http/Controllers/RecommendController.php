<?php

namespace App\Http\Controllers;

use App\Ai\Agents\PromptParser;
use App\Services\CampMatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RecommendController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => 'required|string|max:2000',
            'parsed_criteria' => 'sometimes|array',
            'blocked_weeks' => 'sometimes|array',
            'locked_camps' => 'sometimes|array',
            'exclude_camps' => 'sometimes|array',
        ]);

        try {
            // If we already have parsed criteria (retry), skip the LLM call
            if ($request->has('parsed_criteria')) {
                $parsed = $request->input('parsed_criteria');
            } else {
                $parsedResponse = (new PromptParser)->prompt($request->input('prompt'));
                $parsed = json_decode(json_encode($parsedResponse), true);
            }

            $matcher = new CampMatcher;
            $excludeCamps = $request->input('exclude_camps', []);
            $shortlist = $matcher->buildShortlist($parsed, $excludeCamps);

            $blockedWeeks = $request->input('blocked_weeks', []);
            $lockedCamps = $request->input('locked_camps', []);

            $plan = $matcher->buildPlan($shortlist, $blockedWeeks, $lockedCamps);

            return response()->json([
                'success' => true,
                'data' => $plan,
                'parsed_criteria' => $parsed,
            ]);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'error' => 'Something went wrong while generating recommendations. Please try again.',
            ], 500);
        }
    }
}
