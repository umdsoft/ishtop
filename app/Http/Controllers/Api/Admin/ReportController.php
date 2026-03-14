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
        $query = Report::with(['reporter:id,first_name,last_name', 'reportable']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
}
