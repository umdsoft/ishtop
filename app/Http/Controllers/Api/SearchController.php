<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\City;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use App\Services\GeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(private GeoService $geoService) {}

    public function vacancies(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|min:2|max:200',
            'category' => 'nullable|string',
            'city' => 'nullable|string',
            'work_type' => 'nullable|string',
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0',
            'experience' => 'nullable|string',
            'sort' => 'nullable|in:newest,salary_asc,salary_desc,views',
        ]);

        $query = Vacancy::active()
            ->with('employer:id,company_name,logo_url,verification_level,rating')
            ->when($request->q, fn($q, $v) => $q->search($v))
            ->when($request->category, fn($q, $v) => $q->where('category', $v))
            ->when($request->city, fn($q, $v) => $q->where('city', $v))
            ->when($request->work_type, fn($q, $v) => $q->where('work_type', $v))
            ->when($request->salary_min, fn($q, $v) => $q->where('salary_max', '>=', $v))
            ->when($request->salary_max, fn($q, $v) => $q->where('salary_min', '<=', $v))
            ->when($request->experience, fn($q, $v) => $q->where('experience_required', $v));

        $query = match ($request->sort) {
            'salary_asc' => $query->orderBy('salary_min'),
            'salary_desc' => $query->orderByDesc('salary_max'),
            'views' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_top')->orderByDesc('published_at'),
        };

        $vacancies = $query->paginate($request->per_page ?? 20);

        return response()->json($vacancies);
    }

    public function workers(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'nullable|string|min:2|max:200',
            'city' => 'nullable|string',
            'specialty' => 'nullable|string',
            'work_type' => 'nullable|string',
            'experience_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|min:0',
        ]);

        $workers = WorkerProfile::where('search_status', 'open')
            ->with('user:id,first_name,last_name,username,last_active_at')
            ->when($request->q, function ($q, $v) {
                $q->where(function ($sub) use ($v) {
                    $sub->where('full_name', 'like', "%{$v}%")
                        ->orWhere('specialty', 'like', "%{$v}%")
                        ->orWhere('bio', 'like', "%{$v}%");
                });
            })
            ->when($request->city, fn($q, $v) => $q->where('city', $v))
            ->when($request->specialty, fn($q, $v) => $q->where('specialty', 'like', "%{$v}%"))
            ->when($request->experience_min, fn($q, $v) => $q->where('experience_years', '>=', $v))
            ->when($request->salary_max, fn($q, $v) => $q->where('expected_salary_min', '<=', $v))
            ->when($request->work_type, function ($q, $v) {
                $q->whereJsonContains('work_types', $v);
            })
            ->orderByDesc('updated_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($workers);
    }

    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|integer|min:1|max:50',
            'type' => 'nullable|in:vacancies,workers',
        ]);

        $radius = $request->radius ?? 10;
        $type = $request->type ?? 'vacancies';

        if ($type === 'workers') {
            $results = $this->geoService
                ->nearbyWorkers($request->lat, $request->lng, $radius)
                ->with('user:id,first_name,last_name')
                ->where('search_status', 'open')
                ->paginate(20);
        } else {
            $results = $this->geoService
                ->nearbyVacancies($request->lat, $request->lng, $radius)
                ->with('employer:id,company_name,logo_url')
                ->paginate(20);
        }

        return response()->json($results);
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = Category::active()
            ->orderBy('sort_order')
            ->get(['id', 'slug', 'name_uz', 'name_ru', 'icon', 'sort_order']);

        return response()->json(['categories' => $categories]);
    }

    public function cities(Request $request): JsonResponse
    {
        $cities = City::active()
            ->orderBy('name_uz')
            ->get(['id', 'name_uz', 'name_ru', 'region', 'lat', 'lng']);

        return response()->json(['cities' => $cities]);
    }
}
