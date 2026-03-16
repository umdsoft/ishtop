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
                $q->where('specialization', 'like', "%{$search}%")
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
     * Data comes from two sources with different conventions:
     *   Telegram bot: city = region name, district = city/district name
     *   Miniapp:      city = city name,   district = region name (reversed!)
     *
     * We handle both patterns by building a lookup map from the cities table.
     */
    public function regionalStats(): JsonResponse
    {
        $locations = City::cachedLocations();
        $regions = collect($locations['regions']);
        $allCities = collect($locations['cities']);

        // Build lookup: city name → region key
        $regionKeys = $regions->pluck('key')->toArray();
        $cityNameToRegion = [];
        foreach ($allCities as $c) {
            $cityNameToRegion[$c['name_uz']] = $c['region'];
            $cityNameToRegion[$c['name_ru']] = $c['region'];
        }

        // ── Workers: resolve each record to (region, district_name) ──
        $workerRows = WorkerProfile::select('city', 'district')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->get();

        $workerRegionCounts = [];   // region → count
        $workerDistrictCounts = []; // region → { district_name → count }

        foreach ($workerRows as $row) {
            $resolved = $this->resolveLocation($row->city, $row->district, $regionKeys, $cityNameToRegion);
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
            $resolved = $this->resolveLocation($row->city, $row->district, $regionKeys, $cityNameToRegion);
            if (!$resolved['region']) continue;

            $region = $resolved['region'];
            $district = $resolved['district'];

            $vacancyRegionCounts[$region] = ($vacancyRegionCounts[$region] ?? 0) + 1;

            if ($district) {
                $vacancyDistrictCounts[$region] ??= [];
                $vacancyDistrictCounts[$region][$district] = ($vacancyDistrictCounts[$region][$district] ?? 0) + 1;
            }
        }

        // ── Summary ──
        $totalWorkers = WorkerProfile::count();
        $activeWorkers = WorkerProfile::where('search_status', 'open')->count();
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
     * Handles both data patterns:
     *  Pattern A (Telegram): city="Xorazm viloyati", district="Urganch"
     *  Pattern B (Miniapp):  city="Urganch",         district="Xorazm viloyati"
     *  Pattern C (no district): city="Xorazm viloyati", district=null
     */
    private function resolveLocation(
        ?string $city,
        ?string $district,
        array $regionKeys,
        array $cityNameToRegion
    ): array {
        $city = trim($city ?? '');
        $district = trim($district ?? '');

        // Pattern A: city is a region name
        if (in_array($city, $regionKeys, true)) {
            return [
                'region' => $city,
                'district' => $district ?: null,
            ];
        }

        // Pattern B: district is a region name, city is a district name
        if ($district && in_array($district, $regionKeys, true)) {
            return [
                'region' => $district,
                'district' => $city,
            ];
        }

        // city is a district/city name — resolve via lookup
        if (isset($cityNameToRegion[$city])) {
            return [
                'region' => $cityNameToRegion[$city],
                'district' => $city,
            ];
        }

        // district is a district/city name
        if ($district && isset($cityNameToRegion[$district])) {
            return [
                'region' => $cityNameToRegion[$district],
                'district' => $district,
            ];
        }

        // Fallback — try fuzzy: maybe "Xorazm" without "viloyati"
        foreach ($regionKeys as $rk) {
            if ($city && str_starts_with($rk, $city)) {
                return ['region' => $rk, 'district' => $district ?: null];
            }
        }

        return ['region' => null, 'district' => null];
    }
}
