<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Subscription::with('user:id,first_name,last_name,phone');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }

        $query->latest();
        $subscriptions = $query->paginate($request->input('per_page', 15));

        return response()->json($subscriptions);
    }

    public function show(Subscription $subscription): JsonResponse
    {
        $subscription->load('user');

        return response()->json(['subscription' => $subscription]);
    }

    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string|in:active,cancelled,expired',
            'expires_at' => 'sometimes|date',
        ]);

        $subscription->update($validated);

        return response()->json(['subscription' => $subscription->fresh()]);
    }
}
