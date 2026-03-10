<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebApplicationRequest;
use App\Models\Category;
use App\Models\City;
use App\Models\User;
use App\Models\Vacancy;
use App\Models\WebApplication;
use Illuminate\Http\Request;

class WebController extends Controller
{
    public function home()
    {
        $lang = app()->getLocale();

        $topVacancies = Vacancy::active()
            ->where('is_top', true)
            ->where('top_until', '>', now())
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        $urgentVacancies = Vacancy::active()
            ->where('is_urgent', true)
            ->where('urgent_until', '>', now())
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $latestVacancies = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(12)
            ->get();

        $categories = Category::active()->root()->with('children:id,parent_id,slug')->get();

        // Count vacancies per root category (root slug + child slugs)
        $categorySlugs = $categories->flatMap(fn($cat) => collect([$cat->slug])->merge($cat->children->pluck('slug')))->unique();
        $vacancyCounts = Vacancy::active()
            ->whereIn('category', $categorySlugs)
            ->selectRaw('category, COUNT(*) as cnt')
            ->groupBy('category')
            ->pluck('cnt', 'category');

        foreach ($categories as $cat) {
            $slugs = collect([$cat->slug])->merge($cat->children->pluck('slug'));
            $cat->vacancies_count = $slugs->sum(fn($s) => $vacancyCounts->get($s, 0));
        }

        $stats = [
            'vacancies' => Vacancy::active()->count(),
            'companies' => Vacancy::active()->distinct('employer_id')->count('employer_id'),
            'workers' => User::whereHas('workerProfile')->count(),
        ];

        // Group vacancies by region (viloyat) using cities table
        $regions = \DB::table('vacancies')
            ->join('cities', function ($join) {
                $join->on('vacancies.city', '=', 'cities.name_uz')
                     ->where('cities.is_active', true);
            })
            ->where('vacancies.status', 'active')
            ->where('vacancies.expires_at', '>', now())
            ->select('cities.region')
            ->selectRaw('COUNT(DISTINCT vacancies.id) as count')
            ->groupBy('cities.region')
            ->orderByDesc('count')
            ->pluck('count', 'region');

        return view('website.home', compact(
            'topVacancies', 'urgentVacancies', 'latestVacancies',
            'categories', 'stats', 'regions', 'lang'
        ));
    }

    public function index(Request $request)
    {
        $lang = app()->getLocale();

        // Load categories first (needed for category filter logic)
        $categories = Category::active()->root()->with(['children' => fn($q) => $q->active()])->get();

        // Count vacancies per category slug
        $allSlugs = $categories->flatMap(fn($cat) => collect([$cat->slug])->merge($cat->children->pluck('slug')))->unique();
        $vacancyCounts = Vacancy::active()
            ->whereIn('category', $allSlugs)
            ->selectRaw('category, COUNT(*) as cnt')
            ->groupBy('category')
            ->pluck('cnt', 'category');

        foreach ($categories as $cat) {
            $slugs = collect([$cat->slug])->merge($cat->children->pluck('slug'));
            $cat->vacancies_count = $slugs->sum(fn($s) => $vacancyCounts->get($s, 0));
            foreach ($cat->children as $child) {
                $child->vacancies_count = $vacancyCounts->get($child->slug, 0);
            }
        }

        // Build vacancy query
        $query = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('is_top')
            ->orderByDesc('published_at');

        if ($request->filled('q')) {
            $keyword = '%' . $request->q . '%';
            $query->where(function ($q) use ($keyword) {
                $q->where('title_uz', 'like', $keyword)
                  ->orWhere('title_ru', 'like', $keyword)
                  ->orWhere('description_uz', 'like', $keyword)
                  ->orWhere('description_ru', 'like', $keyword);
            });
        }

        // Category filter: supports single root slug or array of sub-slugs
        $expandedRoot = null;
        $selectedSubs = [];
        if ($request->filled('category')) {
            $catInput = $request->input('category');
            if (is_array($catInput)) {
                $selectedSubs = $catInput;
                // Find root category that owns these child slugs
                foreach ($categories as $cat) {
                    if ($cat->children->pluck('slug')->intersect($catInput)->isNotEmpty()) {
                        $expandedRoot = $cat->slug;
                        // Include root slug — existing vacancies store root slug in DB
                        $query->whereIn('category', array_merge([$cat->slug], $catInput));
                        break;
                    }
                }
                // Fallback: if no root found, filter by given slugs directly
                if (!$expandedRoot) {
                    $query->whereIn('category', $catInput);
                }
            } else {
                $expandedRoot = $catInput;
                $rootCat = $categories->firstWhere('slug', $catInput);
                if ($rootCat && $rootCat->children->isNotEmpty()) {
                    $childSlugs = $rootCat->children->pluck('slug')->toArray();
                    $selectedSubs = $childSlugs;
                    $query->whereIn('category', array_merge([$rootCat->slug], $childSlugs));
                } else {
                    $query->where('category', $catInput);
                }
            }
        }

        if ($request->filled('region')) {
            $regionCities = City::where('region', $request->region)->pluck('name_uz')->unique();
            $query->whereIn('city', $regionCities);
        } elseif ($request->filled('city')) {
            $query->where('city', $request->city);
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

        $vacancies = $query->paginate(20)->withQueryString();

        // Regions for location filter
        $regions = \DB::table('vacancies')
            ->join('cities', function ($join) {
                $join->on('vacancies.city', '=', 'cities.name_uz')
                     ->where('cities.is_active', true);
            })
            ->where('vacancies.status', 'active')
            ->where('vacancies.expires_at', '>', now())
            ->select('cities.region')
            ->selectRaw('COUNT(DISTINCT vacancies.id) as count')
            ->groupBy('cities.region')
            ->orderByDesc('count')
            ->pluck('count', 'region');

        return view('website.vacancies.index', compact(
            'vacancies', 'categories', 'regions', 'lang', 'expandedRoot', 'selectedSubs'
        ));
    }

    public function show(Vacancy $vacancy)
    {
        if (!$vacancy->isActive()) {
            abort(404);
        }

        $lang = app()->getLocale();

        $vacancy->load('employer:id,company_name,logo_url,verification_level,description,phone,rating,rating_count');
        $vacancy->increment('views_count');

        $similarVacancies = Vacancy::active()
            ->where('category', $vacancy->category)
            ->where('id', '!=', $vacancy->id)
            ->with('employer:id,company_name,logo_url,verification_level')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        return view('website.vacancies.show', compact(
            'vacancy', 'similarVacancies', 'lang'
        ));
    }

    public function apply(WebApplicationRequest $request, Vacancy $vacancy)
    {
        if (!$vacancy->isActive()) {
            abort(404);
        }

        WebApplication::create([
            'vacancy_id' => $vacancy->id,
            'name' => $request->validated('name'),
            'phone' => $request->validated('phone'),
            'message' => $request->validated('message'),
            'ip_address' => $request->ip(),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', __('web.application_sent'));
    }

    public function setLocale(string $locale)
    {
        if (!in_array($locale, ['uz', 'ru'])) {
            $locale = 'uz';
        }

        session(['locale' => $locale]);

        return back()->withCookie(cookie('locale', $locale, 60 * 24 * 365));
    }
}
