<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\NewProject;
use App\Models\SystemConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    use ApiResponse;

    /**
     * Active cities & famous localities
     */
    public function cities(): JsonResponse
    {
        $cities = City::with(['famousLocalities' => function ($q) {
            $q->orderBy('id', 'asc');
        }])
        ->where('is_popular', true)
        ->orderBy('display_order', 'asc')
        ->get();

        return $this->successResponse($cities, 'Cities retrieved successfully');
    }

    /**
     * RERA Approved New Projects
     */
    public function newProjects(Request $request): JsonResponse
    {
        $query = NewProject::with('units');

        if ($request->filled('city')) {
            $query->where('city', $request->query('city'));
        }

        $projects = $query->latest()->get();

        return $this->successResponse($projects, 'New projects retrieved successfully');
    }

    /**
     * App System Configuration
     */
    public function systemConfig(): JsonResponse
    {
        $configs = SystemConfig::pluck('config_value', 'config_key');

        return $this->successResponse($configs, 'System configuration retrieved successfully');
    }
}
