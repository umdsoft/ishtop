<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Vacancy;
use App\Models\WorkerProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkerProfile::with('user:id,first_name,last_name,username,phone,avatar_url,is_blocked,created_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('specialty', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search) {
                        $q->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('city')) {
            $query->where('city', $request->city);
        }

        $query->latest();
        $workers = $query->paginate($request->input('per_page', 15));

        return response()->json($workers);
    }

    public function show(WorkerProfile $worker): JsonResponse
    {
        $worker->load('user');

        return response()->json(['worker' => $worker]);
    }

    /**
     * Regional statistics — workers & vacancies grouped by region and district.
     *
     * Only counts workers with completed profiles (has specialty AND city).
     */
    public function regionalStats(): JsonResponse
    {
        $locations = City::cachedLocations();
        $regions = collect($locations['regions']);
        $allCities = collect($locations['cities']);

        // Build lookup maps for location resolution
        $regionKeys = $regions->pluck('key')->toArray();
        $cityNameToRegion = [];
        $normalizedCityLookup = []; // "gurlan" → "Gurlan"
        foreach ($allCities as $c) {
            $cityNameToRegion[$c['name_uz']] = $c['region'];
            $cityNameToRegion[$c['name_ru']] = $c['region'];
            $normalizedCityLookup[mb_strtolower($c['name_uz'])] = $c['name_uz'];
            $normalizedCityLookup[mb_strtolower($c['name_ru'])] = $c['name_ru'];
        }

        // Completed profile = has specialty AND city
        $completedScope = fn ($q) => $q
            ->whereNotNull('specialty')->where('specialty', '!=', '')
            ->whereNotNull('city')->where('city', '!=', '');

        // ── Workers: resolve each completed profile to (region, district) ──
        $workerRows = WorkerProfile::select('city', 'district')
            ->where($completedScope)
            ->get();

        $workerRegionCounts = [];
        $workerDistrictCounts = [];

        foreach ($workerRows as $row) {
            $resolved = $this->resolveLocation($row->city, $row->district, $regionKeys, $cityNameToRegion, $normalizedCityLookup);
            if (!$resolved['region']) continue;

            $region = $resolved['region'];
            $district = $resolved['district'];

            $workerRegionCounts[$region] = ($workerRegionCounts[$region] ?? 0) + 1;

            if ($district) {
                $workerDistrictCounts[$region] ??= [];
                $workerDistrictCounts[$region][$district] = ($workerDistrictCounts[$region][$district] ?? 0) + 1;
            }
        }

        // ── Vacancies (active): resolve each record ──
        $vacancyRows = Vacancy::select('city', 'district')
            ->where('status', 'active')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->get();

        $vacancyRegionCounts = [];
        $vacancyDistrictCounts = [];

        foreach ($vacancyRows as $row) {
            $resolved = $this->resolveLocation($row->city, $row->district, $regionKeys, $cityNameToRegion, $normalizedCityLookup);
            if (!$resolved['region']) continue;

            $region = $resolved['region'];
            $district = $resolved['district'];

            $vacancyRegionCounts[$region] = ($vacancyRegionCounts[$region] ?? 0) + 1;

            if ($district) {
                $vacancyDistrictCounts[$region] ??= [];
                $vacancyDistrictCounts[$region][$district] = ($vacancyDistrictCounts[$region][$district] ?? 0) + 1;
            }
        }

        // ── Summary (only completed profiles) ──
        $totalWorkers = WorkerProfile::where($completedScope)->count();
        $activeWorkers = WorkerProfile::where($completedScope)->where('search_status', 'open')->count();
        $totalActiveVacancies = Vacancy::where('status', 'active')->count();
        $totalApplications = DB::table('applications')->count();

        // ── Build region data ──
        $regionData = $regions->map(function ($region) use (
            $allCities, $workerRegionCounts, $workerDistrictCounts,
            $vacancyRegionCounts, $vacancyDistrictCounts
        ) {
            $key = $region['key'];
            $regionCities = $allCities->where('region', $key)->values();

            $wDistrictMap = $workerDistrictCounts[$key] ?? [];
            $vDistrictMap = $vacancyDistrictCounts[$key] ?? [];

            $districts = $regionCities->map(function ($city) use ($wDistrictMap, $vDistrictMap) {
                return [
                    'id' => $city['id'],
                    'name_uz' => $city['name_uz'],
                    'name_ru' => $city['name_ru'],
                    'type' => $city['type'],
                    'workers_count' => $wDistrictMap[$city['name_uz']] ?? 0,
                    'vacancies_count' => $vDistrictMap[$city['name_uz']] ?? 0,
                ];
            });

            return [
                'key' => $key,
                'name_uz' => $region['name_uz'],
                'name_ru' => $region['name_ru'],
                'workers_count' => $workerRegionCounts[$key] ?? 0,
                'vacancies_count' => $vacancyRegionCounts[$key] ?? 0,
                'districts_count' => $regionCities->count(),
                'districts' => $districts,
            ];
        })->sortByDesc('workers_count')->values();

        return response()->json([
            'summary' => [
                'total_workers' => $totalWorkers,
                'active_workers' => $activeWorkers,
                'total_active_vacancies' => $totalActiveVacancies,
                'total_applications' => $totalApplications,
                'total_regions' => $regions->count(),
            ],
            'regions' => $regionData,
        ]);
    }

    /**
     * Resolve city/district fields to a normalized (region, district_name).
     *
     * Handles multiple data patterns from different sources:
     *  - city="Xorazm viloyati", district="Urganch"  (region + city)
     *  - city="Urganch", district="Xorazm viloyati"  (reversed)
     *  - city="Urganch", district="Xorazm"           (short region)
     *  - city="Gurlan", district=null                 (city-only)
     *  - city="Urganch shahar", district=null         (city with suffix)
     *  - city="Ургенч", district=null                 (Russian name)
     */
    private function resolveLocation(
        ?string $city,
        ?string $district,
        array $regionKeys,
        array $cityNameToRegion,
        array $normalizedCityLookup
    ): array {
        $city = trim($city ?? '');
        $district = trim($district ?? '');

        // Pattern A: city is a region name
        if (in_array($city, $regionKeys, true)) {
            return ['region' => $city, 'district' => $district ?: null];
        }

        // Pattern B: district is a region name, city is a district name
        if ($district && in_array($district, $regionKeys, true)) {
            return ['region' => $district, 'district' => $city];
        }

        // Pattern C: exact city name lookup
        if (isset($cityNameToRegion[$city])) {
            return ['region' => $cityNameToRegion[$city], 'district' => $city];
        }

        // Pattern D: exact district name lookup
        if ($district && isset($cityNameToRegion[$district])) {
            return ['region' => $cityNameToRegion[$district], 'district' => $district];
        }

        // Pattern E: strip suffixes — "Urganch shahar"→"Urganch", "Xiva tumani"→"Xiva"
        $stripped = preg_replace('/\s+(shahri?|tumani?|shahar|город|район)$/iu', '', $city);
        if ($stripped !== $city && isset($cityNameToRegion[$stripped])) {
            return ['region' => $cityNameToRegion[$stripped], 'district' => $stripped];
        }

        // Pattern F: fuzzy region match — "Xorazm" → "Xorazm viloyati", "Хоразм" → ...
        $cityLower = mb_strtolower($city);
        foreach ($regionKeys as $rk) {
            $rkLower = mb_strtolower($rk);
            if ($cityLower && (str_starts_with($rkLower, $cityLower) || str_starts_with($cityLower, mb_strtolower(explode(' ', $rk)[0])))) {
                return ['region' => $rk, 'district' => $district ?: null];
            }
        }

        // Pattern G: case-insensitive + normalized city lookup
        if (isset($normalizedCityLookup[$cityLower])) {
            $canonical = $normalizedCityLookup[$cityLower];
            return ['region' => $cityNameToRegion[$canonical], 'district' => $canonical];
        }

        // Pattern H: strip suffix then case-insensitive
        $strippedLower = mb_strtolower($stripped);
        if ($strippedLower !== $cityLower && isset($normalizedCityLookup[$strippedLower])) {
            $canonical = $normalizedCityLookup[$strippedLower];
            return ['region' => $cityNameToRegion[$canonical], 'district' => $canonical];
        }

        return ['region' => null, 'district' => null];
    }
}
