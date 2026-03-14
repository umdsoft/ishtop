<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = EmployerProfile::with('user:id,first_name,last_name,username,phone,avatar_url,is_blocked,created_at')
            ->withCount('vacancies');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $query->latest();
        $employers = $query->paginate($request->input('per_page', 15));

        return response()->json($employers);
    }

    public function show(EmployerProfile $employer): JsonResponse
    {
        $employer->load(['user', 'vacancies']);
        $employer->loadCount('vacancies');

        return response()->json(['employer' => $employer]);
    }

    public function update(Request $request, EmployerProfile $employer): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'industry' => 'sometimes|nullable|string|max:100',
            'description' => 'sometimes|nullable|string',
        ]);

        $employer->update($validated);

        return response()->json(['employer' => $employer->fresh()]);
    }
}
