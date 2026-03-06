<?php

namespace App\Http\Controllers\Api\Recruiter;

use App\Http\Controllers\Controller;
use App\Models\TalentPoolEntry;
use App\Models\Vacancy;
use App\Services\MatchingService;
use App\Services\SubscriptionLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CandidateController extends Controller
{
    public function __construct(
        private MatchingService $matchingService,
        private SubscriptionLimitService $limitService,
    ) {}

    public function recommended(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check subscription feature
        if (!$this->limitService->hasFeature($user, 'talent_pool')) {
            return response()->json([
                'message' => 'Nomzodlar bazasi faqat Recruiter Pro va undan yuqori rejalarda mavjud.',
                'limit_reached' => true,
            ], 403);
        }

        $request->validate([
            'vacancy_id' => 'required|uuid',
        ]);

        $employer = $user->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancy = Vacancy::where('id', $request->vacancy_id)
            ->where('employer_id', $employer->id)
            ->firstOrFail();

        $candidates = $this->matchingService->getRecommendedCandidates(
            $vacancy,
            $request->per_page ?? 20
        );

        // Mark which candidates are already in talent pool
        $poolIds = TalentPoolEntry::where('recruiter_user_id', $user->id)
            ->pluck('worker_profile_id')
            ->toArray();

        $candidates->getCollection()->transform(function ($candidate) use ($poolIds) {
            $candidate->is_in_pool = in_array($candidate->id, $poolIds);
            return $candidate;
        });

        return response()->json([
            'candidates' => $candidates,
            'vacancy' => [
                'id' => $vacancy->id,
                'title_uz' => $vacancy->title_uz,
                'title_ru' => $vacancy->title_ru,
            ],
        ]);
    }

    public function vacancies(Request $request): JsonResponse
    {
        $employer = $request->user()->employerProfile;

        if (!$employer) {
            return response()->json(['message' => 'Employer profili kerak'], 403);
        }

        $vacancies = Vacancy::where('employer_id', $employer->id)
            ->where('status', 'active')
            ->select('id', 'title_uz', 'title_ru', 'city', 'work_type')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['vacancies' => $vacancies]);
    }
}
