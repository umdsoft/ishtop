<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email yoki parol noto\'g\'ri'], 401);
        }

        if (!$user->employerProfile) {
            return response()->json(['message' => 'Recruiter profili topilmadi'], 403);
        }

        $token = $user->createToken('recruiter-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('employerProfile'),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|regex:/^\+998\d{9}$/',
            'company_name' => 'required|string|max:300',
            'industry' => 'nullable|string|max:50',
        ]);

        $user = User::create([
            'telegram_id' => 0,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'is_verified' => true,
            'referral_code' => User::generateReferralCode(),
        ]);

        EmployerProfile::create([
            'user_id' => $user->id,
            'company_name' => $request->company_name,
            'industry' => $request->industry,
            'phone' => $request->phone,
        ]);

        $token = $user->createToken('recruiter-api')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user->load('employerProfile'),
        ], 201);
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->load(['employerProfile', 'subscriptions' => function ($q) {
            $q->where('status', 'active')->where('expires_at', '>', now())->latest()->limit(1);
        }]);

        return response()->json([
            'user' => $user,
            'employer' => $user->employerProfile,
            'active_subscription' => $user->activeSubscription(),
        ]);
    }
}
