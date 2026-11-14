<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\SavedProperty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SavedPropertyController extends Controller
{
    use ApiResponse;

    /**
     * List user's saved / bookmarked properties
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $savedIds = SavedProperty::where('user_id', $user->id)->pluck('property_id');

        $properties = Property::with(['images', 'coverImage'])
            ->whereIn('id', $savedIds)
            ->where('is_deleted', false)
            ->latest()
            ->get();

        return $this->successResponse($properties, 'Saved properties retrieved successfully');
    }

    /**
     * Toggle Bookmark / Save
     */
    public function toggle(int $propertyId, Request $request): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);

        $existing = SavedProperty::where('user_id', $user->id)->where('property_id', $propertyId)->first();

        if ($existing) {
            $existing->delete();
            $isSaved = false;
            $msg = 'Property removed from saved list';
        } else {
            SavedProperty::create(['user_id' => $user->id, 'property_id' => $propertyId]);
            $isSaved = true;
            $msg = 'Property saved successfully';
        }

        $totalSaved = SavedProperty::where('user_id', $user->id)->count();

        return $this->successResponse([
            'is_saved' => $isSaved,
            'isSaved' => $isSaved,
            'total_saved' => $totalSaved,
            'totalSaved' => $totalSaved,
        ], $msg);
    }
}

