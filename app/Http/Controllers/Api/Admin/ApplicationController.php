<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Application::with([
            'vacancy:id,title_uz,title_ru,employer_id',
            'vacancy.employer:id,company_name',
            'worker:id,user_id',
            'worker.user:id,first_name,last_name,phone,avatar_url',
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('worker.user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('vacancy_id')) {
            $query->where('vacancy_id', $request->vacancy_id);
        }

        $query->latest();
        $applications = $query->paginate($request->input('per_page', 15));

        return response()->json($applications);
    }

    public function show(Application $application): JsonResponse
    {
        $application->load(['vacancy', 'vacancy.employer', 'worker.user']);

        return response()->json(['application' => $application]);
    }
}
