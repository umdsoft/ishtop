<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Report::with(['reporter:id,first_name,last_name,phone', 'reportable']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reason', 'like', "%{$search}%")
                    ->orWhereHas('reporter', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        $query->latest();
        $reports = $query->paginate($request->input('per_page', 15));

        return response()->json($reports);
    }

    public function show(Report $report): JsonResponse
    {
        $report->load(['reporter', 'reportable']);

        return response()->json(['report' => $report]);
    }

    public function resolve(Report $report): JsonResponse
    {
        $report->update(['status' => 'resolved']);

        return response()->json(['message' => 'Shikoyat hal qilindi', 'report' => $report->fresh()]);
    }

    public function dismiss(Report $report): JsonResponse
    {
        $report->update(['status' => 'dismissed']);

        return response()->json(['message' => 'Shikoyat rad etildi', 'report' => $report->fresh()]);
    }
}
