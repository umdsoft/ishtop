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
     */
    public function regionalStats(): JsonResponse
    {
        // Get all regions from cities table
        $locations = City::cachedLocations();
        $regions = collect($locations['regions']);
        $cities = collect($locations['cities']);

        // Workers count per region (city field stores region name)
        $workersByRegion = WorkerProfile::select('city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->pluck('count', 'city');

        // Workers count per district
        $workersByDistrict = WorkerProfile::select('district', 'city', DB::raw('COUNT(*) as count'))
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->groupBy('district', 'city')
            ->get()
            ->groupBy('city')
            ->map(fn($items) => $items->pluck('count', 'district'));

        // Active vacancies count per region (city field stores region name)
        $vacanciesByRegion = Vacancy::select('city', DB::raw('COUNT(*) as count'))
            ->where('status', 'active')
            ->whereNotNull('city')
            ->where('city', '!=', '')
            ->groupBy('city')
            ->pluck('count', 'city');

        // Active vacancies count per district
        $vacanciesByDistrict = Vacancy::select('district', 'city', DB::raw('COUNT(*) as count'))
            ->where('status', 'active')
            ->whereNotNull('district')
            ->where('district', '!=', '')
            ->groupBy('district', 'city')
            ->get()
            ->groupBy('city')
            ->map(fn($items) => $items->pluck('count', 'district'));

        // Total counts
        $totalWorkers = WorkerProfile::count();
        $totalActiveVacancies = Vacancy::where('status', 'active')->count();
        $totalApplications = DB::table('applications')->count();
        $activeWorkers = WorkerProfile::where('search_status', 'open')->count();

        // Build region data
        $regionData = $regions->map(function ($region) use (
            $cities, $workersByRegion, $workersByDistrict, $vacanciesByRegion, $vacanciesByDistrict
        ) {
            $regionKey = $region['key'];

            // Get districts for this region from cities table
            $regionCities = $cities->where('region', $regionKey)->values();

            $districts = $regionCities->map(function ($city) use ($regionKey, $workersByDistrict, $vacanciesByDistrict) {
                $districtWorkers = $workersByDistrict->get($regionKey);
                $districtVacancies = $vacanciesByDistrict->get($regionKey);

                return [
                    'id' => $city['id'],
                    'name_uz' => $city['name_uz'],
                    'name_ru' => $city['name_ru'],
                    'type' => $city['type'],
                    'workers_count' => $districtWorkers ? ($districtWorkers[$city['name_uz']] ?? 0) : 0,
                    'vacancies_count' => $districtVacancies ? ($districtVacancies[$city['name_uz']] ?? 0) : 0,
                ];
            });

            return [
                'key' => $regionKey,
                'name_uz' => $region['name_uz'],
                'name_ru' => $region['name_ru'],
                'workers_count' => $workersByRegion[$regionKey] ?? 0,
                'vacancies_count' => $vacanciesByRegion[$regionKey] ?? 0,
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
}
