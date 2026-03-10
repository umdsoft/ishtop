<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WebApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WebApiController extends Controller
{
    /**
     * GET /api/web/home
     * Home page data: stats, categories, top/urgent/latest vacancies, regions
     */
    public function home(): JsonResponse
    {
        $lang = app()->getLocale();

        $topVacancies = Vacancy::active()
            ->where('is_top', true)
            ->where('top_until', '>', now())
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(8)
            ->get()
            ->map(fn($v) => $this->formatVacancy($v, $lang));

        $urgentVacancies = Vacancy::active()
            ->where('is_urgent', true)
            ->where('urgent_until', '>', now())
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get()
            ->map(fn($v) => $this->formatVacancy($v, $lang));

        $latestVacancies = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(12)
            ->get()
            ->map(fn($v) => $this->formatVacancy($v, $lang));

        $categories = Category::active()->root()
            ->with(['children' => fn($q) => $q->active()])
            ->get();

        // Count vacancies per category
        $allSlugs = $categories->flatMap(fn($cat) => collect([$cat->slug])->merge($cat->children->pluck('slug')))->unique();
        $vacancyCounts = Vacancy::active()
            ->whereIn('category', $allSlugs)
            ->selectRaw('category, COUNT(*) as cnt')
            ->groupBy('category')
            ->pluck('cnt', 'category');

        $categoriesData = $categories->map(function ($cat) use ($vacancyCounts, $lang) {
            $slugs = collect([$cat->slug])->merge($cat->children->pluck('slug'));
            return [
                'slug' => $cat->slug,
                'name' => $lang === 'ru' ? ($cat->name_ru ?: $cat->name_uz) : $cat->name_uz,
                'icon' => $cat->icon,
                'vacancies_count' => $slugs->sum(fn($s) => $vacancyCounts->get($s, 0)),
                'children' => $cat->children->map(fn($child) => [
                    'slug' => $child->slug,
                    'name' => $lang === 'ru' ? ($child->name_ru ?: $child->name_uz) : $child->name_uz,
                    'vacancies_count' => $vacancyCounts->get($child->slug, 0),
                ]),
            ];
        });

        $stats = cache()->remember('home_stats', 300, function () {
            return [
                'vacancies' => Vacancy::active()->count(),
                'companies' => Vacancy::active()->distinct('employer_id')->count('employer_id'),
                'workers' => User::whereHas('workerProfile')->count(),
            ];
        });

        $regions = $this->getStructuredRegions();

        return response()->json([
            'stats' => $stats,
            'categories' => $categoriesData,
            'topVacancies' => $topVacancies,
            'urgentVacancies' => $urgentVacancies,
            'latestVacancies' => $latestVacancies,
            'regions' => $regions,
        ]);
    }

    /**
     * GET /api/web/vacancies
     * Filtered vacancy list + filter metadata
     */
    public function vacancies(Request $request): JsonResponse
    {
        $lang = app()->getLocale();

        // Build vacancy query
        $query = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('is_top')
            ->orderByDesc('published_at');

        if ($request->filled('q')) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $request->q);
            $keyword = '%' . $escaped . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('title_uz', 'like', $keyword)
                  ->orWhere('title_ru', 'like', $keyword)
                  ->orWhere('description_uz', 'like', $keyword)
                  ->orWhere('description_ru', 'like', $keyword);
            });
        }

        if ($request->filled('category')) {
            $catInput = $request->input('category');

            if (is_array($catInput)) {
                // Subcategory checkboxes: category[]=slug1&category[]=slug2
                $query->whereIn('category', $catInput);
            } else {
                // Single root category: category=slug → include all children
                $categories = Category::active()->root()->with(['children' => fn($q) => $q->active()])->get();
                $rootCat = $categories->firstWhere('slug', $catInput);
                if ($rootCat && $rootCat->children->isNotEmpty()) {
                    $childSlugs = $rootCat->children->pluck('slug')->toArray();
                    $query->whereIn('category', array_merge([$rootCat->slug], $childSlugs));
                } else {
                    $query->where('category', $catInput);
                }
            }
        }

        if ($request->filled('city')) {
            $cityInput = $request->input('city');
            if (is_array($cityInput)) {
                $query->whereIn('city', $cityInput);
            } else {
                $query->where('city', $cityInput);
            }
        } elseif ($request->filled('region')) {
            // Region filter — include all cities within region
            $regionCities = City::where('region', $request->region)->pluck('name_uz')->unique();
            $query->where(function ($q) use ($request, $regionCities) {
                $q->where('city', $request->region)
                  ->orWhereIn('city', $regionCities);
            });
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        if ($request->filled('salary_min')) {
            $query->where('salary_max', '>=', (int) $request->salary_min);
        }

        if ($request->filled('salary_max')) {
            $query->where('salary_min', '<=', (int) $request->salary_max);
        }

        if ($request->filled('sort')) {
            match ($request->sort) {
                'salary_asc' => $query->reorder()->orderByRaw('COALESCE(salary_min, 0) ASC'),
                'salary_desc' => $query->reorder()->orderByRaw('COALESCE(salary_max, 0) DESC'),
                default => null,
            };
        }

        $paginated = $query->paginate(min($request->integer('per_page', 20), 100));

        // Format data
        $data = $paginated->getCollection()->map(fn($v) => $this->formatVacancy($v, $lang));

        // Filter metadata (categories + regions)
        $categories = Category::active()->root()->with(['children' => fn($q) => $q->active()])->get();
        $allSlugs = $categories->flatMap(fn($cat) => collect([$cat->slug])->merge($cat->children->pluck('slug')))->unique();
        $vacancyCounts = Vacancy::active()
            ->whereIn('category', $allSlugs)
            ->selectRaw('category, COUNT(*) as cnt')
            ->groupBy('category')
            ->pluck('cnt', 'category');

        $categoriesData = $categories->map(function ($cat) use ($vacancyCounts, $lang) {
            $slugs = collect([$cat->slug])->merge($cat->children->pluck('slug'));
            return [
                'slug' => $cat->slug,
                'name' => $lang === 'ru' ? ($cat->name_ru ?: $cat->name_uz) : $cat->name_uz,
                'vacancies_count' => $slugs->sum(fn($s) => $vacancyCounts->get($s, 0)),
                'children' => $cat->children->map(fn($child) => [
                    'slug' => $child->slug,
                    'name' => $lang === 'ru' ? ($child->name_ru ?: $child->name_uz) : $child->name_uz,
                    'vacancies_count' => $vacancyCounts->get($child->slug, 0),
                ]),
            ];
        });

        $regions = $this->getStructuredRegions();

        return response()->json([
            'data' => $data,
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'meta' => [
                'categories' => $categoriesData,
                'regions' => $regions,
            ],
        ]);
    }

    /**
     * GET /api/web/vacancies/{id}
     * Vacancy detail + similar vacancies
     */
    public function show(string $id): JsonResponse
    {
        $vacancy = Vacancy::with('employer:id,company_name,logo_url,verification_level,description,phone,rating,rating_count')
            ->find($id);

        if (!$vacancy || !$vacancy->isActive()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $vacancy->increment('views_count');
        $lang = app()->getLocale();

        $similarVacancies = Vacancy::active()
            ->where('category', $vacancy->category)
            ->where('id', '!=', $vacancy->id)
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get()
            ->map(fn($v) => $this->formatVacancy($v, $lang));

        return response()->json([
            'vacancy' => $this->formatVacancyDetail($vacancy, $lang),
            'similar' => $similarVacancies,
        ]);
    }

    /**
     * POST /api/web/vacancies/{id}/apply
     * Submit web application
     */
    public function apply(Request $request, string $id): JsonResponse
    {
        $vacancy = Vacancy::find($id);

        if (!$vacancy || !$vacancy->isActive()) {
            return response()->json(['message' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'message' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        WebApplication::create([
            'vacancy_id' => $vacancy->id,
            'name' => $request->input('name'),
            'phone' => $request->input('phone'),
            'message' => $request->input('message'),
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get structured regions with cities/tumans and vacancy counts
     */
    protected function getStructuredRegions(): array
    {
        return cache()->remember('structured_regions', 300, function () {
            $lang = app()->getLocale();
            $allCities = City::where('is_active', true)
                ->get(['name_uz', 'name_ru', 'region', 'type']);

            $regionNames = $allCities->pluck('region')->unique()->sort()->values();

            $cityCounts = Vacancy::active()
                ->selectRaw('city, COUNT(*) as cnt')
                ->groupBy('city')
                ->pluck('cnt', 'city');

            return $regionNames->map(function ($region) use ($allCities, $cityCounts, $lang) {
                $cities = $allCities->where('region', $region);
                $cityNames = $cities->pluck('name_uz');

                $totalCount = ($cityCounts->get($region, 0))
                    + $cityNames->sum(fn($name) => $cityCounts->get($name, 0));

                $citiesData = $cities->map(function ($city) use ($cityCounts) {
                    return [
                        'name' => $city->name_uz,
                        'name_ru' => $city->name_ru,
                        'type' => $city->type,
                        'count' => $cityCounts->get($city->name_uz, 0),
                    ];
                })->sortByDesc('count')->values();

                return [
                    'name' => $region,
                    'count' => $totalCount,
                    'cities' => $citiesData,
                ];
            })->values()->toArray();
        });
    }

    /**
     * Format vacancy for list view
     */
    protected function formatVacancy(Vacancy $v, string $lang): array
    {
        return [
            'id' => $v->id,
            'title' => $lang === 'ru' ? ($v->title_ru ?: $v->title_uz) : $v->title_uz,
            'company_name' => $v->company_name,
            'city' => $v->city,
            'work_type' => $v->work_type?->value ?? $v->work_type,
            'salary_type' => $v->salary_type,
            'salary_min' => $v->salary_min,
            'salary_max' => $v->salary_max,
            'experience_required' => $v->experience_required,
            'is_top' => $v->isTopActive(),
            'is_urgent' => (bool) $v->is_urgent && $v->urgent_until?->isFuture(),
            'views_count' => $v->views_count ?? 0,
            'published_at' => $v->published_at?->toIso8601String(),
            'employer' => $v->employer ? [
                'id' => $v->employer->id,
                'company_name' => $v->employer->company_name,
                'logo_url' => $v->employer->logo_url,
                'verification_level' => $v->employer->verification_level,
            ] : null,
        ];
    }

    /**
     * Format vacancy for detail view (full data)
     */
    protected function formatVacancyDetail(Vacancy $v, string $lang): array
    {
        $base = $this->formatVacancy($v, $lang);
        $base['description'] = $lang === 'ru' ? ($v->description_ru ?: $v->description_uz) : $v->description_uz;
        $base['requirements'] = $lang === 'ru' ? ($v->requirements_ru ?: $v->requirements_uz) : $v->requirements_uz;
        $base['responsibilities'] = $lang === 'ru' ? ($v->responsibilities_ru ?: $v->responsibilities_uz) : $v->responsibilities_uz;
        $base['category'] = $v->category;
        $base['experience'] = $v->experience;
        $base['latitude'] = $v->latitude;
        $base['longitude'] = $v->longitude;
        $base['employer'] = $v->employer ? [
            'id' => $v->employer->id,
            'company_name' => $v->employer->company_name,
            'logo_url' => $v->employer->logo_url,
            'verification_level' => $v->employer->verification_level,
            'description' => $v->employer->description,
            'phone' => $v->employer->phone,
            'rating' => $v->employer->rating,
            'rating_count' => $v->employer->rating_count,
        ] : null;

        return $base;
    }
}
