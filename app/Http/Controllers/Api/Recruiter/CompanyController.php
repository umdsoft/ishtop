<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\EmployerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $companies = $request->user()
            ->employerProfiles()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'companies' => $companies,
            'active_id' => $request->user()->active_employer_id,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:300',
            'industry' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:300',
            'employees_count' => 'nullable|string|max:20',
        ]);

        $company = EmployerProfile::create(array_merge($validated, [
            'user_id' => $request->user()->id,
        ]));

        // Auto-set as active if this is the user's first company
        if (!$request->user()->active_employer_id) {
            $request->user()->update(['active_employer_id' => $company->id]);
        }

        return response()->json(['company' => $company], 201);
    }

    public function update(Request $request, string $company): JsonResponse
    {
        $companyModel = $request->user()
            ->employerProfiles()
            ->where('id', $company)
            ->firstOrFail();

        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:300',
            'industry' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:300',
            'employees_count' => 'nullable|string|max:20',
            'stir_number' => 'nullable|string|max:20',
        ]);

        $companyModel->update($validated);

        return response()->json(['company' => $companyModel->fresh()]);
    }

    public function destroy(Request $request, string $company): JsonResponse
    {
        $user = $request->user();
        $companyModel = $user->employerProfiles()
            ->where('id', $company)
            ->firstOrFail();

        if ($user->employerProfiles()->count() <= 1) {
            return response()->json([
                'message' => 'Oxirgi kompaniyani o\'chirib bo\'lmaydi',
            ], 422);
        }

        // If deleting the active company, switch to another one
        if ($user->active_employer_id === $companyModel->id) {
            $nextCompany = $user->employerProfiles()
                ->where('id', '!=', $companyModel->id)
                ->first();
            $user->update(['active_employer_id' => $nextCompany->id]);
        }

        $companyModel->delete();

        return response()->json(['message' => 'Kompaniya o\'chirildi']);
    }

    public function switch(Request $request, string $company): JsonResponse
    {
        $user = $request->user();

        $exists = $user->employerProfiles()
            ->where('id', $company)
            ->exists();

        if (!$exists) {
            return response()->json(['message' => 'Kompaniya topilmadi'], 404);
        }

        $user->update(['active_employer_id' => $company]);
        $user->refresh();

        return response()->json([
            'message' => 'Aktiv kompaniya almashtirildi',
            'active_employer' => $user->employerProfile,
        ]);
    }
}
