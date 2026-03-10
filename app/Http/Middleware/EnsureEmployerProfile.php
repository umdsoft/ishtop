<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployerProfile
{
    /**
     * Foydalanuvchining active employer profili borligini tekshiradi.
     * Profil topilsa, request'ga employer attribute sifatida qo'shadi.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Avtorizatsiya kerak'], 401);
        }

        $employer = $user->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $request->merge(['_employer' => $employer]);

        return $next($request);
    }
}
