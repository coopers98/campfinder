<?php

namespace App\Http\Controllers;

use App\Ai\Agents\CampRecommender;
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
            $response = (new CampRecommender)->prompt($request->input('prompt'));

            return response()->json([
                'success' => true,
                'data' => [
                    'children' => $response['children'],
                    'sibling_overlaps' => $response['sibling_overlaps'],
                    'total_estimated_cost_cents' => $response['total_estimated_cost_cents'],
                    'notes' => $response['notes'],
                ],
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
