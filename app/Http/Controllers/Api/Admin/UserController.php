<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function stats(): JsonResponse
    {
        return response()->json([
            'total_users' => User::count(),
            'today_users' => User::whereDate('created_at', today())->count(),
            'active_users' => User::where('is_blocked', false)->count(),
            'blocked_users' => User::where('is_blocked', true)->count(),
            'workers' => WorkerProfile::count(),
            'employers' => EmployerProfile::count(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = User::with(['workerProfile:id,user_id', 'employerProfiles:id,user_id,company_name'])
            ->withCount(['workerProfile', 'employerProfiles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_blocked')) {
            $query->where('is_blocked', $request->boolean('is_blocked'));
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $query->latest();
        $users = $query->paginate($request->input('per_page', 15));

        return response()->json($users);
    }

    public function show(User $user): JsonResponse
    {
        $user->load([
            'workerProfile',
            'employerProfiles' => fn($q) => $q->withCount('vacancies'),
            'roles',
        ]);
        $user->loadCount([
            'referrals',
            'payments' => fn($q) => $q->where('status', 'completed'),
        ]);

        // Worker applications count
        if ($user->workerProfile) {
            $user->workerProfile->loadCount('applications');
        }

        return response()->json(['user' => $user]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|nullable|string|max:100',
            'email' => 'sometimes|nullable|email|unique:users,email,' . $user->id,
            'is_blocked' => 'sometimes|boolean',
            'is_verified' => 'sometimes|boolean',
        ]);

        $user->update($validated);

        return response()->json(['user' => $user->fresh()]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return response()->json(['message' => 'Foydalanuvchi o\'chirildi']);
    }

    public function toggleBlock(User $user): JsonResponse
    {
        $user->update(['is_blocked' => !$user->is_blocked]);

        return response()->json([
            'message' => $user->is_blocked ? 'Foydalanuvchi bloklandi' : 'Foydalanuvchi blokdan chiqarildi',
            'user' => $user->fresh(),
        ]);
    }
}
