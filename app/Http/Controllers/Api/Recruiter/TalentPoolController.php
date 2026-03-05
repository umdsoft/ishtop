<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\TalentPoolEntry;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalentPoolController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $entries = TalentPoolEntry::where('recruiter_user_id', $userId)
            ->with('workerProfile:id,full_name,city,specialty,experience_years,expected_salary_min,expected_salary_max,photo_url')
            ->when($request->q, function ($q, $v) {
                $q->whereHas('workerProfile', function ($sub) use ($v) {
                    $sub->where('full_name', 'like', "%{$v}%")
                        ->orWhere('specialty', 'like', "%{$v}%");
                });
            })
            ->when($request->tags, function ($q, $v) {
                foreach ((array) $v as $tag) {
                    $q->whereJsonContains('tags', $tag);
                }
            })
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($entries);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'worker_profile_id' => 'required|uuid|exists:worker_profiles,id',
            'notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'source' => 'nullable|string|max:50',
        ]);

        $userId = $request->user()->id;

        $existing = TalentPoolEntry::where('recruiter_user_id', $userId)
            ->where('worker_profile_id', $request->worker_profile_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Bu nomzod allaqachon talent pulda'], 422);
        }

        $entry = TalentPoolEntry::create([
            'recruiter_user_id' => $userId,
            'worker_profile_id' => $request->worker_profile_id,
            'notes' => $request->notes,
            'tags' => $request->tags ?? [],
            'source' => $request->source ?? 'manual',
        ]);

        $entry->load('workerProfile:id,full_name,city,specialty');

        return response()->json(['entry' => $entry], 201);
    }

    public function destroy(Request $request, string $entry): JsonResponse
    {
        $entryModel = TalentPoolEntry::where('id', $entry)
            ->where('recruiter_user_id', $request->user()->id)
            ->firstOrFail();

        $entryModel->delete();

        return response()->json(['message' => 'Talent puldan olib tashlandi']);
    }
}
