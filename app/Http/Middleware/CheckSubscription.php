<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    public function handle(Request $request, Closure $next, string ...$plans): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Auth kerak'], 401);
        }

        $subscription = $user->activeSubscription();

        if (empty($plans)) {
            return $next($request);
        }

        if (!$subscription || !$subscription->plan || !in_array($subscription->plan->value, $plans)) {
            return response()->json([
                'message' => 'Bu funksiya uchun obuna kerak',
                'required_plans' => $plans,
            ], 403);
        }

        return $next($request);
    }
}
