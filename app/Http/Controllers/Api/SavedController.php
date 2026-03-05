<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SavedItem;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'nullable|in:vacancy,worker',
        ]);

        $userId = $request->user()->id;

        $query = SavedItem::where('user_id', $userId);

        if ($request->type === 'vacancy') {
            $query->where('saveable_type', Vacancy::class);
        } elseif ($request->type === 'worker') {
            $query->where('saveable_type', WorkerProfile::class);
        }

        $saved = $query->with('saveable')
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($saved);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'saveable_type' => 'required|in:vacancy,worker',
            'saveable_id' => 'required|uuid',
        ]);

        $userId = $request->user()->id;

        $modelClass = match ($request->saveable_type) {
            'vacancy' => Vacancy::class,
            'worker' => WorkerProfile::class,
        };

        $model = $modelClass::findOrFail($request->saveable_id);

        $existing = SavedItem::where('user_id', $userId)
            ->where('saveable_type', $modelClass)
            ->where('saveable_id', $model->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Allaqachon saqlangan'], 422);
        }

        $saved = SavedItem::create([
            'user_id' => $userId,
            'saveable_type' => $modelClass,
            'saveable_id' => $model->id,
        ]);

        return response()->json(['saved' => $saved], 201);
    }

    public function destroy(Request $request, string $saved): JsonResponse
    {
        $item = SavedItem::where('id', $saved)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $item->delete();

        return response()->json(['message' => 'Olib tashlandi']);
    }
}
