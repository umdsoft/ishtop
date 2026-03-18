<?php

namespace App\Services;

use App\Enums\WorkType;
use App\Models\City;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Support\Collection;

class MatchingService
{
    private const CATEGORY_KEYWORDS = [
        'it'           => ['developer', 'dasturchi', 'programmer', 'backend', 'frontend', 'fullstack', 'devops', 'qa', 'tester', 'data', 'analyst', 'software', 'mobile', 'web', 'php', 'java', 'python', 'react', 'vue', 'laravel', 'node'],
        'sales'        => ['seller', 'sotuvchi', 'sales', 'manager', 'konsultant', 'savdo', 'marketing', 'smm', 'reklama'],
        'finance'      => ['buxgalter', 'accountant', 'finance', 'moliya', 'audit', 'bank', 'kassir', 'economist', 'hisobchi'],
        'education'    => ['teacher', 'o\'qituvchi', 'tutor', 'mentor', 'ta\'lim', 'trainer', 'pedagog', 'repetitor'],
        'medicine'     => ['doctor', 'shifokor', 'nurse', 'hamshira', 'tibbiy', 'farmatsevt', 'stomatolog'],
        'construction' => ['qurilish', 'engineer', 'muhandis', 'architect', 'welder', 'electrician', 'santexnik', 'prorab'],
        'transport'    => ['driver', 'haydovchi', 'logistics', 'logistika', 'ombor', 'courier', 'yetkazib', 'ekspeditor'],
        'food'         => ['oshpaz', 'cook', 'chef', 'waiter', 'ofitsiant', 'barista', 'pekarna', 'konditer'],
        'other'        => [],
    ];

    public function __construct(
        private GeoService $geoService
    ) {}

    // ══════════════════════════════════════════════════════════════
    //  PUBLIC — Matching queries
    // ══════════════════════════════════════════════════════════════

    public function findMatchesForWorker(WorkerProfile $worker, int $limit = 10): Collection
    {
        $query = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level');

        // Category filter: parent-child aware
        // Sub-category selected (it-frontend) → match: it-frontend, it (parent), NULL
        // Parent selected (it) → match: it, it-frontend, it-backend, ... (all children), NULL
        $preferredCategories = $worker->preferred_categories ?? [];
        if (!empty($preferredCategories)) {
            $expandedSlugs = [];
            $selectedParents = [];

            foreach ($preferredCategories as $cat) {
                $cat = mb_strtolower($cat);
                $expandedSlugs[] = $cat;
                $parent = $this->extractParentSlug($cat);
                if ($parent !== $cat) {
                    // Sub-category: also include its parent
                    $expandedSlugs[] = $parent;
                } else {
                    // Main category explicitly selected
                    $selectedParents[] = $cat;
                }
            }

            $expandedSlugs = array_unique($expandedSlugs);

            $query->where(function ($q) use ($expandedSlugs, $selectedParents) {
                $q->whereIn('category', $expandedSlugs)
                  ->orWhereNull('category');
                // Explicitly selected parents also match all their children
                foreach ($selectedParents as $parent) {
                    $q->orWhere('category', 'LIKE', $parent . '-%');
                }
            });
        }

        // Salary: only exclude vacancies drastically below expectations
        if ($worker->expected_salary_min) {
            $query->where(function ($q) use ($worker) {
                $q->whereNull('salary_max')
                  ->orWhere('salary_max', '>=', (int) ($worker->expected_salary_min * 0.5));
            });
        }

        // Add distance if worker has coordinates
        if ($worker->latitude && $worker->longitude) {
            $haversine = GeoService::haversineFormula();
            $query->selectRaw("vacancies.*, {$haversine} as distance_km", [
                (float) $worker->latitude, (float) $worker->longitude, (float) $worker->latitude,
            ]);
        }

        $candidates = $query->orderByDesc('published_at')->limit($limit * 5)->get();

        return $candidates->map(function ($vacancy) use ($worker) {
            $vacancy->match_score = $this->calculateMatchScore($worker, $vacancy);
            return $vacancy;
        })->sort(function ($a, $b) {
            $scoreDiff = ($b->match_score ?? 0) - ($a->match_score ?? 0);
            if ($scoreDiff !== 0) return $scoreDiff;
            // Same score — nearest first
            return ($a->distance_km ?? PHP_INT_MAX) <=> ($b->distance_km ?? PHP_INT_MAX);
        })->take($limit)->values();
    }

    public function findMatchesForVacancy(Vacancy $vacancy, int $limit = 20): Collection
    {
        $query = WorkerProfile::active();

        // Category filter: parent-child aware
        $this->applyCategoryFilterForWorkers($query, $vacancy->category);

        // Location filter: same viloyat (region) for on-site, no filter for remote
        $this->applyLocationFilterForWorkers($query, $vacancy);

        $candidates = $query->limit($limit * 3)->get();

        return $candidates->map(function ($worker) use ($vacancy) {
            $worker->match_score = $this->calculateMatchScore($worker, $vacancy);
            return $worker;
        })->sortByDesc('match_score')->take($limit)->values();
    }

    /**
     * Find recommended candidates for a vacancy with scoring, excluding already applied.
     */
    public function getRecommendedCandidates(Vacancy $vacancy, int $perPage = 20): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = WorkerProfile::query()
            ->whereIn('search_status', ['open', 'passive'])
            ->whereDoesntHave('applications', fn($q) => $q->where('vacancy_id', $vacancy->id));

        // Category filter: parent-child aware
        $this->applyCategoryFilterForWorkers($query, $vacancy->category);

        // Location filter: same viloyat for on-site, no filter for remote
        $this->applyLocationFilterForWorkers($query, $vacancy);

        $workers = $query
            ->select('id', 'full_name', 'city', 'district', 'specialty', 'experience_years', 'expected_salary_min', 'expected_salary_max', 'work_types', 'skills', 'preferred_categories', 'photo_url', 'search_status', 'latitude', 'longitude')
            ->paginate($perPage);

        // Calculate match score for each worker
        $workers->getCollection()->transform(function ($worker) use ($vacancy) {
            $worker->match_score = $this->calculateMatchScore($worker, $vacancy);
            return $worker;
        });

        // Sort by match_score descending (within the page)
        $sorted = $workers->getCollection()->sortByDesc('match_score')->values();
        $workers->setCollection($sorted);

        return $workers;
    }

    /**
     * Quick count of potential recommended candidates (pre-filter only, no scoring).
     */
    public function countRecommendedCandidates(Vacancy $vacancy): int
    {
        $query = WorkerProfile::query()
            ->whereIn('search_status', ['open', 'passive'])
            ->whereDoesntHave('applications', fn($q) => $q->where('vacancy_id', $vacancy->id));

        // Category filter: parent-child aware (same as getRecommendedCandidates)
        $this->applyCategoryFilterForWorkers($query, $vacancy->category);

        // Location filter: same viloyat for on-site, no filter for remote
        $this->applyLocationFilterForWorkers($query, $vacancy);

        return $query->count();
    }

    /**
     * Batch count recommended candidates for multiple vacancies.
     * Uses individual counts with category-aware filtering for consistency.
     *
     * @return array<string, int> vacancy_id => count
     */
    public function countRecommendedCandidatesBatch(Collection $vacancies): array
    {
        if ($vacancies->isEmpty()) {
            return [];
        }

        $result = [];
        foreach ($vacancies as $vacancy) {
            $result[$vacancy->id] = $this->countRecommendedCandidates($vacancy);
        }

        return $result;
    }

    // ══════════════════════════════════════════════════════════════
    //  PUBLIC — Score calculation (8 criteria, 100 points total)
    // ══════════════════════════════════════════════════════════════

    public function calculateMatchScore(WorkerProfile $worker, Vacancy $vacancy): int
    {
        $score = 0.0;

        $score += $this->skillsScore($worker, $vacancy);            // max 20
        $score += $this->locationScore($worker, $vacancy);          // max 15
        $score += $this->salaryScore($worker, $vacancy);            // max 15
        $score += $this->experienceScore($worker, $vacancy);        // max 15
        $score += $this->categorySpecialtyScore($worker, $vacancy); // max 15
        $score += $this->workTypeScore($worker, $vacancy);          // max 10
        $score += $this->searchStatusScore($worker);                // max 5
        $score += $this->titleRelevanceScore($worker, $vacancy);    // max 5

        return (int) round(min($score, 100));
    }

    // ══════════════════════════════════════════════════════════════
    //  PUBLIC — Detailed analysis for recruiter view
    // ══════════════════════════════════════════════════════════════

    public function getDetailedAnalysis(WorkerProfile $worker, Vacancy $vacancy): array
    {
        $criteria = [];

        // 1. Skills (20)
        $skillsScore = $this->skillsScore($worker, $vacancy);
        $workerSkills = $worker->skills ?? [];
        $criteria[] = [
            'key' => 'skills',
            'label' => 'Ko\'nikmalar',
            'matched' => $skillsScore >= 10,
            'score' => $skillsScore,
            'max' => 20,
            'worker_value' => !empty($workerSkills) ? implode(', ', array_slice($workerSkills, 0, 6)) : null,
            'vacancy_value' => null,
            'detail' => $this->skillsDetail($worker, $vacancy, $skillsScore),
        ];

        // 2. Location (15)
        $locScore = $this->locationScore($worker, $vacancy);
        $workerLoc = $worker->city ?: null;
        if ($worker->district) $workerLoc .= ", {$worker->district}";
        $vacancyLoc = $vacancy->city ?: null;
        if ($vacancy->district) $vacancyLoc .= ", {$vacancy->district}";
        $criteria[] = [
            'key' => 'location',
            'label' => 'Joylashuv',
            'matched' => $locScore >= 10,
            'score' => $locScore,
            'max' => 15,
            'worker_value' => $workerLoc,
            'vacancy_value' => $vacancyLoc,
            'detail' => $this->locationDetail($worker, $vacancy, $locScore),
        ];

        // 3. Salary (15)
        $salScore = $this->salaryScore($worker, $vacancy);
        $criteria[] = [
            'key' => 'salary',
            'label' => 'Maosh',
            'matched' => $salScore >= 10,
            'score' => $salScore,
            'max' => 15,
            'worker_value' => $this->formatSalaryRange($worker->expected_salary_min, $worker->expected_salary_max),
            'vacancy_value' => $this->formatSalaryRange($vacancy->salary_min, $vacancy->salary_max),
            'detail' => $this->salaryDetail($worker, $vacancy, $salScore),
        ];

        // 4. Experience (15)
        $expScore = $this->experienceScore($worker, $vacancy);
        $expLabel = $this->experienceLabel($vacancy->experience_required);
        $criteria[] = [
            'key' => 'experience',
            'label' => 'Tajriba',
            'matched' => $expScore >= 12,
            'score' => $expScore,
            'max' => 15,
            'worker_value' => $worker->experience_years ? "{$worker->experience_years} yil" : null,
            'vacancy_value' => $expLabel,
            'detail' => $worker->experience_years
                ? "Nomzod: {$worker->experience_years} yil, Talab: {$expLabel}"
                : 'Tajriba ko\'rsatilmagan',
        ];

        // 5. Category/Specialty (15)
        $catScore = $this->categorySpecialtyScore($worker, $vacancy);
        $criteria[] = [
            'key' => 'category',
            'label' => 'Soha mosligi',
            'matched' => $catScore >= 10,
            'score' => $catScore,
            'max' => 15,
            'worker_value' => $worker->specialty ?: null,
            'vacancy_value' => $vacancy->category ?: null,
            'detail' => $catScore >= 15 ? 'Soha to\'liq mos'
                : ($catScore >= 10 ? 'Soha qisman mos'
                : ($worker->specialty && $vacancy->category ? 'Soha mos emas' : 'Ma\'lumot yetarli emas')),
        ];

        // 6. Work type (10)
        $wtScore = $this->workTypeScore($worker, $vacancy);
        $workerWorkTypeLabels = $worker->work_types
            ? array_map(fn($wt) => WorkType::tryFrom($wt)?->label() ?? $wt, $worker->work_types)
            : [];
        $vacancyWorkTypeLabel = $vacancy->work_type?->label();
        $criteria[] = [
            'key' => 'work_type',
            'label' => 'Ish turi',
            'matched' => $wtScore > 0,
            'score' => $wtScore,
            'max' => 10,
            'worker_value' => $workerWorkTypeLabels ? implode(', ', $workerWorkTypeLabels) : null,
            'vacancy_value' => $vacancyWorkTypeLabel,
            'detail' => $wtScore > 0
                ? 'Ish turi mos'
                : ($vacancy->work_type ? "Vakansiya: {$vacancyWorkTypeLabel}" : 'Ma\'lumot yetarli emas'),
        ];

        // 7. Search status (5)
        $ssScore = $this->searchStatusScore($worker);
        $statusLabel = $worker->search_status === 'open' || (is_object($worker->search_status) && $worker->search_status->value === 'open')
            ? 'Faol qidiruvda'
            : 'Passiv';
        $criteria[] = [
            'key' => 'search_status',
            'label' => 'Qidiruv holati',
            'matched' => $ssScore >= 5,
            'score' => $ssScore,
            'max' => 5,
            'worker_value' => $statusLabel,
            'vacancy_value' => null,
            'detail' => $ssScore >= 5 ? 'Nomzod faol ish qidirmoqda' : 'Nomzod passiv holatda',
        ];

        // 8. Title relevance (5)
        $trScore = $this->titleRelevanceScore($worker, $vacancy);
        $criteria[] = [
            'key' => 'title_relevance',
            'label' => 'Lavozim mosligi',
            'matched' => $trScore >= 3,
            'score' => $trScore,
            'max' => 5,
            'worker_value' => $worker->specialty ?: null,
            'vacancy_value' => $vacancy->title_uz ?: $vacancy->title_ru,
            'detail' => $trScore >= 5 ? 'Lavozim nomi mos'
                : ($trScore >= 3 ? 'Lavozim qisman mos'
                : ($worker->specialty ? 'Lavozim mos emas' : 'Ma\'lumot yetarli emas')),
        ];

        $totalScore = array_sum(array_column($criteria, 'score'));

        $recommendation = match (true) {
            $totalScore >= 80 => 'Juda mos nomzod',
            $totalScore >= 60 => 'Yaxshi moslik',
            $totalScore >= 40 => 'Qisman mos',
            default => 'Moslik past',
        };

        return [
            'overall_score' => (int) round(min($totalScore, 100)),
            'recommendation' => $recommendation,
            'criteria' => $criteria,
        ];
    }

    // ══════════════════════════════════════════════════════════════
    //  PRIVATE — 8 scoring methods
    // ══════════════════════════════════════════════════════════════

    /**
     * 1. Skills match (max 20 pts)
     * Compare worker's skills array against vacancy requirements + title text.
     */
    private function skillsScore(WorkerProfile $worker, Vacancy $vacancy): float
    {
        $workerSkills = $worker->skills ?? [];
        if (empty($workerSkills)) return 0;

        $vacancyText = mb_strtolower(implode(' ', array_filter([
            $vacancy->requirements_uz,
            $vacancy->requirements_ru,
            $vacancy->title_uz,
            $vacancy->title_ru,
            $vacancy->description_uz,
            $vacancy->description_ru,
        ])));

        if (empty(trim($vacancyText))) return 0;

        $matchedCount = 0;
        foreach ($workerSkills as $skill) {
            $skillLower = mb_strtolower(trim($skill));
            if (mb_strlen($skillLower) >= 2 && mb_strpos($vacancyText, $skillLower) !== false) {
                $matchedCount++;
            }
        }

        $ratio = $matchedCount / count($workerSkills);
        // 50%+ skills matched = full 20 pts
        return min(20, round($ratio * 2 * 20, 1));
    }

    /**
     * 2. Location match (max 15 pts)
     * City = 10, District bonus = +5, Geo fallback if city doesn't match.
     */
    private function locationScore(WorkerProfile $worker, Vacancy $vacancy): float
    {
        $score = 0.0;

        $workerCity = $worker->city ? $this->normalizeCityName($worker->city) : null;
        $vacancyCity = $vacancy->city ? $this->normalizeCityName($vacancy->city) : null;

        $cityMatch = $workerCity && $vacancyCity
            && mb_strtolower($workerCity) === mb_strtolower($vacancyCity);

        if ($cityMatch) {
            $score += 10;

            // District bonus
            if ($worker->district && $vacancy->district
                && mb_strtolower($worker->district) === mb_strtolower($vacancy->district)) {
                $score += 5;
            }
        } elseif ($worker->latitude && $worker->longitude && $vacancy->latitude && $vacancy->longitude) {
            // Geo-proximity fallback
            $distance = $this->geoService->distanceBetween(
                (float) $worker->latitude, (float) $worker->longitude,
                (float) $vacancy->latitude, (float) $vacancy->longitude
            );

            $score += match (true) {
                $distance <= 5   => 10,
                $distance <= 15  => 7,
                $distance <= 30  => 4,
                $distance <= 50  => 2,
                default          => 0,
            };
        }

        return $score;
    }

    /**
     * 3. Salary overlap (max 15 pts)
     * Graduated scoring based on range overlap ratio.
     */
    private function salaryScore(WorkerProfile $worker, Vacancy $vacancy): float
    {
        $wMin = $worker->expected_salary_min;
        $wMax = $worker->expected_salary_max;
        $vMin = $vacancy->salary_min;
        $vMax = $vacancy->salary_max;

        // No salary info = benefit of the doubt
        if (!$wMin && !$wMax) return 15;
        if (!$vMin && !$vMax) return 15;

        // Normalize missing bounds
        $wMin = $wMin ?: (int) ($wMax * 0.6);
        $wMax = $wMax ?: (int) ($wMin * 1.5);
        $vMin = $vMin ?: (int) ($vMax * 0.6);
        $vMax = $vMax ?: (int) ($vMin * 1.5);

        $overlapStart = max($wMin, $vMin);
        $overlapEnd = min($wMax, $vMax);

        if ($overlapStart > $overlapEnd) {
            // No overlap — partial credit based on gap size
            $gap = $overlapStart - $overlapEnd;
            $rangeSize = max($wMax - $wMin, $vMax - $vMin, 1);
            $gapRatio = $gap / $rangeSize;

            if ($gapRatio <= 0.2) return 7;
            if ($gapRatio <= 0.4) return 3;
            return 0;
        }

        // Overlap exists — proportional credit
        $overlapSize = $overlapEnd - $overlapStart;
        $smallerRange = min(max($wMax - $wMin, 1), max($vMax - $vMin, 1));
        $ratio = min($overlapSize / $smallerRange, 1.0);

        return round($ratio * 15, 1);
    }

    /**
     * 4. Experience match (max 15 pts)
     * 5-tier graduated scoring.
     */
    private function experienceScore(WorkerProfile $worker, Vacancy $vacancy): float
    {
        $required = $this->parseRequiredExperience($vacancy->experience_required);
        $actual = $worker->experience_years ?? 0;

        if ($required === 0) return 15; // No experience needed

        $diff = $actual - $required;

        return match (true) {
            $diff >= 0  => 15,
            $diff >= -1 => 12,
            $diff >= -2 => 8,
            $diff >= -3 => 4,
            default     => 0,
        };
    }

    /**
     * 5. Category/Specialty match (max 15 pts)
     * Two strategies: preferred_categories slug matching + specialty keyword matching.
     * Takes the higher score.
     */
    private function categorySpecialtyScore(WorkerProfile $worker, Vacancy $vacancy): float
    {
        $category = mb_strtolower($vacancy->category ?? '');
        if (empty($category)) return 0;

        $score = 0.0;

        // Strategy 1: preferred_categories slug matching (parent-child aware)
        $preferredCategories = $worker->preferred_categories ?? [];
        if (!empty($preferredCategories)) {
            $catParent = $this->extractParentSlug($category);

            foreach ($preferredCategories as $pref) {
                $pref = mb_strtolower($pref);

                // Exact match (e.g., it-frontend = it-frontend)
                if ($pref === $category) {
                    $score = 15;
                    break;
                }

                $prefParent = $this->extractParentSlug($pref);

                // Parent-child (e.g., worker: it-frontend, vacancy: it — or vice versa)
                if ($pref === $catParent || $prefParent === $category) {
                    $score = max($score, 12);
                }
                // Siblings under same parent (e.g., worker: it-frontend, vacancy: it-backend)
                elseif ($prefParent === $catParent) {
                    $score = max($score, 8);
                }
            }
        }

        // Strategy 2: specialty text vs category keywords (fallback)
        $specialty = mb_strtolower($worker->specialty ?? '');
        if (!empty($specialty) && $score < 15) {
            if (mb_strpos($specialty, $category) !== false) {
                $score = max($score, 15);
            } else {
                $keywords = self::CATEGORY_KEYWORDS[$category] ?? [];
                // For sub-categories, also check parent's keywords
                if (empty($keywords)) {
                    $parentSlug = $this->extractParentSlug($category);
                    if ($parentSlug !== $category) {
                        $keywords = self::CATEGORY_KEYWORDS[$parentSlug] ?? [];
                    }
                }

                $matchCount = 0;
                foreach ($keywords as $keyword) {
                    if (mb_strpos($specialty, $keyword) !== false) {
                        $matchCount++;
                    }
                }

                if ($matchCount >= 2) $score = max($score, 15);
                elseif ($matchCount === 1) $score = max($score, 10);
            }
        }

        return $score;
    }

    /**
     * Apply category-aware filter to WorkerProfile query based on vacancy category.
     *
     * Only matches workers who explicitly selected a matching category.
     * Workers with no preferred_categories are excluded (incomplete profile).
     *
     * - Subcategory (e.g., education-teacher): exact match + parent match
     * - Parent category (e.g., education): parent + all children (education-*)
     */
    private function applyCategoryFilterForWorkers($query, ?string $vacancyCategory): void
    {
        if (empty($vacancyCategory)) return;

        $category = mb_strtolower($vacancyCategory);
        $parent = $this->extractParentSlug($category);
        $isSubCategory = $parent !== $category;

        $query->where(function ($q) use ($parent, $category, $isSubCategory) {
            // Exact category match
            $q->whereJsonContains('preferred_categories', $category);

            if ($isSubCategory) {
                // Subcategory: also match workers who selected the parent
                $q->orWhereJsonContains('preferred_categories', $parent);
            } else {
                // Parent category: also match workers who selected any child
                $q->orWhere('preferred_categories', 'LIKE', '%"' . $parent . '-%');
            }
        });
    }

    /**
     * Apply region-aware location filter to WorkerProfile query.
     *
     * - remote vacancies: no location filter (scoring handles priority)
     * - on-site vacancies: only workers from same viloyat (region) or no city
     */
    private function applyLocationFilterForWorkers($query, Vacancy $vacancy): void
    {
        if (empty($vacancy->city)) return;

        // Remote jobs accept candidates from anywhere — scoring prioritizes local
        $workType = $vacancy->work_type instanceof WorkType
            ? $vacancy->work_type
            : WorkType::tryFrom($vacancy->work_type ?? '');

        if ($workType === WorkType::REMOTE) return;

        // Find the region for this vacancy's city
        $regionCities = $this->getRegionCitiesForCity($vacancy->city);

        if (empty($regionCities)) {
            // Fallback: exact city match (try both original and normalized)
            $normalized = $this->normalizeCityName($vacancy->city);
            $query->where(function ($q) use ($vacancy, $normalized) {
                $q->where('city', $vacancy->city)
                  ->orWhere('city', $normalized)
                  ->orWhereNull('city')
                  ->orWhere('city', '');
            });
            return;
        }

        $query->where(function ($q) use ($regionCities) {
            $q->whereIn('city', $regionCities)
              ->orWhereNull('city')
              ->orWhere('city', '');
        });
    }

    /**
     * Get all city names in the same region as the given city.
     * Cached for 1 hour since cities rarely change.
     *
     * @return string[] city names in the same viloyat
     */
    private function getRegionCitiesForCity(string $cityName): array
    {
        return cache()->remember("region_cities_{$cityName}", 3600, function () use ($cityName) {
            $city = City::where('name_uz', $cityName)
                ->orWhere('name_ru', $cityName)
                ->first();

            // Fallback: strip common suffixes like "sh.", "shahar", "shahri", "tumani", "t."
            if (!$city) {
                $cleaned = $this->normalizeCityName($cityName);
                if ($cleaned !== $cityName) {
                    $city = City::where('name_uz', $cleaned)
                        ->orWhere('name_ru', $cleaned)
                        ->first();
                }
            }

            if (!$city || !$city->region) return [];

            return City::where('region', $city->region)
                ->get(['name_uz', 'name_ru'])
                ->flatMap(fn($c) => [$c->name_uz, $c->name_ru])
                ->filter()
                ->unique()
                ->values()
                ->all();
        });
    }

    /**
     * Normalize city name by stripping common Uzbek/Russian suffixes.
     * "Urganch sh." → "Urganch", "Toshkent shahri" → "Toshkent"
     */
    private function normalizeCityName(string $name): string
    {
        return trim(preg_replace('/\s+(sh\.?|shahar|shahri|tumani|t\.|город|г\.)$/ui', '', trim($name)));
    }

    /**
     * Extract parent slug from a category slug.
     * e.g., 'it-frontend' → 'it', 'sales-shop' → 'sales', 'it' → 'it'
     */
    private function extractParentSlug(string $slug): string
    {
        $pos = strpos($slug, '-');
        return $pos !== false ? substr($slug, 0, $pos) : $slug;
    }

    /**
     * 6. Work type match (max 10 pts)
     */
    private function workTypeScore(WorkerProfile $worker, Vacancy $vacancy): float
    {
        if (!$worker->work_types || !$vacancy->work_type) return 0;
        return in_array($vacancy->work_type->value, $worker->work_types) ? 10 : 0;
    }

    /**
     * 7. Search status preference (max 5 pts)
     * Open = 5, Passive = 2.
     */
    private function searchStatusScore(WorkerProfile $worker): float
    {
        $status = is_object($worker->search_status) ? $worker->search_status->value : $worker->search_status;

        return match ($status) {
            'open'    => 5,
            'passive' => 2,
            default   => 0,
        };
    }

    /**
     * 8. Title relevance (max 5 pts)
     * Worker specialty words vs vacancy title.
     */
    private function titleRelevanceScore(WorkerProfile $worker, Vacancy $vacancy): float
    {
        $specialty = mb_strtolower($worker->specialty ?? '');
        $title = mb_strtolower(implode(' ', array_filter([
            $vacancy->title_uz,
            $vacancy->title_ru,
        ])));

        if (empty($specialty) || empty($title)) return 0;

        // Split specialty into words (3+ chars)
        $specialtyWords = array_filter(
            preg_split('/[\s\/\-,\.]+/', $specialty),
            fn($w) => mb_strlen($w) >= 3
        );

        if (empty($specialtyWords)) return 0;

        $matchCount = 0;
        foreach ($specialtyWords as $word) {
            if (mb_strpos($title, $word) !== false) {
                $matchCount++;
            }
        }

        $ratio = $matchCount / count($specialtyWords);
        if ($ratio >= 0.5) return 5;
        if ($ratio > 0) return 3;
        return 0;
    }

    // ══════════════════════════════════════════════════════════════
    //  PRIVATE — Helpers
    // ══════════════════════════════════════════════════════════════

    private function parseRequiredExperience(?string $exp): int
    {
        return match ($exp) {
            'no_experience', 'intern' => 0,
            'junior', '0-1'          => 1,
            '1-3'                    => 1,
            'mid', '2-4'             => 3,
            '3-5'                    => 3,
            'senior', '5+'           => 5,
            default                  => 0,
        };
    }

    private function experienceLabel(?string $exp): string
    {
        return match ($exp) {
            'no_experience' => 'Tajriba kerak emas',
            'intern'        => 'Stajiyor',
            'junior'        => 'Junior (1-2 yil)',
            'mid'           => 'O\'rta (2-4 yil)',
            'senior'        => 'Katta tajriba (4+ yil)',
            '0-1'           => '0-1 yil',
            '1-3'           => '1-3 yil',
            '3-5'           => '3-5 yil',
            '5+'            => '5+ yil',
            default         => 'ko\'rsatilmagan',
        };
    }

    private function formatSalaryRange(?int $min, ?int $max): ?string
    {
        if (!$min && !$max) return null;
        if ($min && $max) return number_format($min) . ' — ' . number_format($max);
        if ($min) return number_format($min) . ' dan';
        return number_format($max) . ' gacha';
    }

    private function skillsDetail(WorkerProfile $worker, Vacancy $vacancy, float $score): string
    {
        $workerSkills = $worker->skills ?? [];
        if (empty($workerSkills)) return 'Nomzod ko\'nikmalari ko\'rsatilmagan';

        $vacancyText = mb_strtolower(implode(' ', array_filter([
            $vacancy->requirements_uz,
            $vacancy->requirements_ru,
            $vacancy->title_uz,
            $vacancy->title_ru,
        ])));

        if (empty(trim($vacancyText))) return 'Vakansiyada talablar ko\'rsatilmagan';

        $matched = [];
        foreach ($workerSkills as $skill) {
            $skillLower = mb_strtolower(trim($skill));
            if (mb_strlen($skillLower) >= 2 && mb_strpos($vacancyText, $skillLower) !== false) {
                $matched[] = $skill;
            }
        }

        if (count($matched) > 0) {
            return 'Mos: ' . implode(', ', array_slice($matched, 0, 5));
        }

        return 'Ko\'nikmalar mos kelmadi';
    }

    private function locationDetail(WorkerProfile $worker, Vacancy $vacancy, float $score): string
    {
        if ($score >= 15) {
            return "{$worker->city}, {$worker->district} — to'liq mos";
        }
        if ($score >= 10) {
            return "{$worker->city} — shahar mos";
        }
        if ($score > 0 && $worker->latitude && $vacancy->latitude) {
            $distance = $this->geoService->distanceBetween(
                (float) $worker->latitude, (float) $worker->longitude,
                (float) $vacancy->latitude, (float) $vacancy->longitude
            );
            return "Masofa: {$distance} km";
        }
        if ($worker->city && $vacancy->city) {
            return "Nomzod: {$worker->city}, Vakansiya: {$vacancy->city}";
        }
        return 'Ma\'lumot yetarli emas';
    }

    private function salaryDetail(WorkerProfile $worker, Vacancy $vacancy, float $score): string
    {
        $wMin = $worker->expected_salary_min;
        $wMax = $worker->expected_salary_max;
        $vMin = $vacancy->salary_min;
        $vMax = $vacancy->salary_max;

        if (!$wMin && !$wMax && !$vMin && !$vMax) return 'Maosh ma\'lumotlari ko\'rsatilmagan';
        if ($score >= 13) return 'Maosh kutishlari to\'liq mos';
        if ($score >= 7) return 'Maosh kutishlari qisman mos';
        if ($score > 0) return 'Maosh kutishlarida kichik farq bor';
        return 'Maosh kutishlari mos emas';
    }
}
