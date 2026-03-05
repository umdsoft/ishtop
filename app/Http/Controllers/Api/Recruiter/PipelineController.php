<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Enums\ApplicationStage;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\CandidateTag;
use App\Models\RecruiterNote;
use App\Models\RecruiterTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function updateStage(Request $request, string $application): JsonResponse
    {
        $request->validate([
            'stage' => 'required|string|in:new,reviewed,shortlisted,interview,offered,hired,rejected',
            'rejected_reason' => 'nullable|string|max:300',
        ]);

        $app = Application::findOrFail($application);
        $stage = ApplicationStage::from($request->stage);

        $app->moveToStage($stage);

        if ($request->stage === 'rejected' && $request->rejected_reason) {
            $app->update(['rejected_reason' => $request->rejected_reason]);
        }

        return response()->json(['application' => $app->fresh()]);
    }

    public function rate(Request $request, string $application): JsonResponse
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $app = Application::findOrFail($application);
        $app->update(['recruiter_rating' => $request->rating]);

        return response()->json(['application' => $app->fresh()]);
    }

    public function addNote(Request $request, string $application): JsonResponse
    {
        $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $app = Application::findOrFail($application);

        $note = RecruiterNote::create([
            'application_id' => $app->id,
            'user_id' => $request->user()->id,
            'note' => $request->note,
        ]);

        return response()->json(['note' => $note], 201);
    }

    public function addTags(Request $request, string $application): JsonResponse
    {
        $request->validate([
            'tags' => 'required|array',
            'tags.*' => 'string|max:50',
        ]);

        $app = Application::findOrFail($application);
        $userId = $request->user()->id;

        foreach ($request->tags as $tagName) {
            $tag = RecruiterTag::firstOrCreate(
                ['user_id' => $userId, 'name' => $tagName],
                ['color' => '#' . substr(md5($tagName), 0, 6)]
            );

            CandidateTag::firstOrCreate([
                'application_id' => $app->id,
                'tag_id' => $tag->id,
            ]);

            $tag->increment('usage_count');
        }

        $tags = CandidateTag::where('application_id', $app->id)
            ->with('tag:id,name,color')
            ->get();

        return response()->json(['tags' => $tags]);
    }
}
