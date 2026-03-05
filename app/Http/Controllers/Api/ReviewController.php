<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'employer_profile_id' => 'required|uuid|exists:employer_profiles,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = $request->user()->id;

        $existing = Review::where('employer_profile_id', $request->employer_profile_id)
            ->where('worker_user_id', $userId)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Siz allaqachon sharh qoldirgansiz'], 422);
        }

        $review = Review::create([
            'employer_profile_id' => $request->employer_profile_id,
            'worker_user_id' => $userId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $employer = EmployerProfile::find($request->employer_profile_id);
        $avgRating = Review::where('employer_profile_id', $employer->id)->avg('rating');
        $ratingCount = Review::where('employer_profile_id', $employer->id)->count();
        $employer->update([
            'rating' => round($avgRating, 2),
            'rating_count' => $ratingCount,
        ]);

        $review->load('worker:id,first_name,last_name');

        return response()->json(['review' => $review], 201);
    }

    public function byEmployer(Request $request, string $employer): JsonResponse
    {
        $reviews = Review::where('employer_profile_id', $employer)
            ->with('worker:id,first_name,last_name,username')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        $avgRating = Review::where('employer_profile_id', $employer)->avg('rating');

        return response()->json([
            'reviews' => $reviews,
            'avg_rating' => round($avgRating ?? 0, 2),
        ]);
    }
}
