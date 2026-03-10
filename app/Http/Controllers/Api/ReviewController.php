<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Models\EmployerProfile;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = $request->user()->id;

        $existing = Review::where('employer_profile_id', $validated['employer_profile_id'])
            ->where('worker_user_id', $userId)
            ->exists();

        if ($existing) {
            return response()->json(['message' => 'Siz allaqachon sharh qoldirgansiz'], 422);
        }

        $review = DB::transaction(function () use ($validated, $userId) {
            $review = Review::create([
                'employer_profile_id' => $validated['employer_profile_id'],
                'worker_user_id' => $userId,
                'rating' => $validated['rating'],
                'comment' => $validated['comment'] ?? null,
            ]);

            $employer = EmployerProfile::findOrFail($validated['employer_profile_id']);
            $stats = Review::where('employer_profile_id', $employer->id)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total_count')
                ->first();

            $employer->update([
                'rating' => round($stats->avg_rating, 2),
                'rating_count' => $stats->total_count,
            ]);

            return $review;
        });

        $review->load('worker:id,first_name,last_name');

        return response()->json(['review' => $review], 201);
    }

    public function byEmployer(Request $request, string $employer): JsonResponse
    {
        $reviews = Review::where('employer_profile_id', $employer)
            ->with('worker:id,first_name,last_name,username')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        // Use denormalized rating from employer profile (updated on every review store)
        $avgRating = EmployerProfile::where('id', $employer)->value('rating');

        return response()->json([
            'reviews' => $reviews,
            'avg_rating' => round($avgRating ?? 0, 2),
        ]);
    }
}
