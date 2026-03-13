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
        ]);

        try {
            // Stage 1: Parse free text into structured criteria (gpt-4o-mini, ~2-3s)
            $parsedResponse = (new PromptParser)->prompt($request->input('prompt'));
            $parsed = json_decode(json_encode($parsedResponse), true);

            // Stage 2: PHP-side scoring, shortlist building, and plan assembly (instant)
            $matcher = new CampMatcher;
            $shortlist = $matcher->buildShortlist($parsed);
            $plan = $matcher->buildPlan($shortlist);

            return response()->json([
                'success' => true,
                'data' => $plan,
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
