<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProContactUnlock;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\SavedProperty;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    use ApiResponse;

    /**
     * Browse properties feed with multi-tier filters & pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::with(['images', 'coverImage'])
            ->active();

        // 1. Text Search across title, locality, society, and unique code
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhere('locality', 'LIKE', "%{$search}%")
                  ->orWhere('society_or_yard', 'LIKE', "%{$search}%")
                  ->orWhere('property_code', 'LIKE', "%{$search}%");
            });
        }

        // 2. City Filter
        if ($request->filled('city')) {
            $query->where('city', $request->query('city'));
        }

        // 3. Intent (Sell, Rent, Bank Auction)
        if ($request->filled('intent')) {
            $query->where('intent', $request->query('intent'));
        }

        // 4. Asset Type (Property, Vehicle)
        $assetType = $request->query('assetType', $request->query('asset_type'));
        if ($assetType) {
            $query->where('asset_type', $assetType);
        }

        // 5. Sub Type (Apartment, Commercial, Truck, SUV)
        $subType = $request->query('subType', $request->query('property_sub_type'));
        if ($subType) {
            $query->where('property_sub_type', $subType);
        }

        // 6. Locality
        if ($request->filled('locality')) {
            $query->where('locality', 'LIKE', '%' . $request->query('locality') . '%');
        }

        // 7. BHK
        if ($request->filled('bhk')) {
            $query->where('bhk', $request->query('bhk'));
        }

        // 8. Min / Max Price
        $minPrice = $request->query('minPrice', $request->query('min_price'));
        if ($minPrice) {
            $query->where('price', '>=', (float) $minPrice);
        }

        $maxPrice = $request->query('maxPrice', $request->query('max_price'));
        if ($maxPrice) {
            $query->where('price', '<=', (float) $maxPrice);
        }

        // 9. Sorting
        $sort = $request->query('sort', $request->query('sort_by', 'Newest'));
        switch (strtolower(str_replace(' ', '', $sort))) {
            case 'price:lowtohigh':
            case 'price_low_high':
                $query->orderBy('price', 'asc');
                break;
            case 'price:hightolow':
            case 'price_high_low':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('views_count', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $perPage = min((int) $request->query('limit', $request->query('per_page', 20)), 50);
        $properties = $query->paginate($perPage);

        // Transform for Contact Privacy Engine
        $user = $request->user('sanctum');
        $unlockedIds = $user ? ProContactUnlock::where('user_id', $user->id)->pluck('property_id')->toArray() : [];
        $savedIds = $user ? SavedProperty::where('user_id', $user->id)->pluck('property_id')->toArray() : [];

        $transformed = $properties->getCollection()->map(function ($property) use ($unlockedIds, $savedIds) {
            $isUnlocked = in_array($property->id, $unlockedIds);
            $isSaved = in_array($property->id, $savedIds);
            return $this->formatPropertyResponse($property, $isUnlocked, $isSaved);
        });

        return $this->successResponse(
            $transformed,
            'Properties retrieved successfully',
            200,
            [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        );
    }

    /**
     * Get property details by ID
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $property = Property::with(['images', 'user:id,name,role,city'])->findOrFail($id);
        $property->increment('views_count');

        $user = $request->user('sanctum');
        $isUnlocked = false;
        $isSaved = false;

        if ($user) {
            $isUnlocked = ProContactUnlock::where('user_id', $user->id)
                ->where('property_id', $property->id)
                ->exists();
            $isSaved = SavedProperty::where('user_id', $user->id)
                ->where('property_id', $property->id)
                ->exists();
        }

        return $this->successResponse(
            $this->formatPropertyResponse($property, $isUnlocked, $isSaved),
            'Property details retrieved successfully'
        );
    }

    /**
     * Direct Property Code Lookup (e.g. Code '032')
     */
    public function getByCode(string $code, Request $request): JsonResponse
    {
        $property = Property::with(['images', 'user:id,name,role,city'])
            ->where('property_code', trim($code))
            ->active()
            ->first();

        if (!$property) {
            return $this->errorResponse("No active property found with Property Code: {$code}", 404);
        }

        $property->increment('views_count');

        $user = $request->user('sanctum');
        $isUnlocked = $user ? ProContactUnlock::where('user_id', $user->id)->where('property_id', $property->id)->exists() : false;
        $isSaved = $user ? SavedProperty::where('user_id', $user->id)->where('property_id', $property->id)->exists() : false;

        return $this->successResponse(
            $this->formatPropertyResponse($property, $isUnlocked, $isSaved),
            'Property found successfully'
        );
    }

    /**
     * Post a new property (defaults to 'Under Review')
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:220'],
            'intent' => ['required', 'string', 'in:Sell,Rent,Bank Auction'],
            'asset_type' => ['nullable', 'string', 'in:Property,Vehicle'],
            'assetType' => ['nullable', 'string', 'in:Property,Vehicle'],
            'property_sub_type' => ['nullable', 'string', 'max:80'],
            'subType' => ['nullable', 'string', 'max:80'],
            'price' => ['required', 'numeric', 'min:1'],
            'price_display' => ['nullable', 'string', 'max:60'],
            'priceDisplay' => ['nullable', 'string', 'max:60'],
            'society_or_yard' => ['nullable', 'string', 'max:140'],
            'society' => ['nullable', 'string', 'max:140'],
            'locality' => ['required', 'string', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'state' => ['nullable', 'string', 'max:80'],
            'pincode' => ['nullable', 'string', 'max:10'],
            'bhk' => ['nullable', 'string', 'max:20'],
            'bathrooms' => ['nullable', 'string', 'max:20'],
            'balconies' => ['nullable', 'string', 'max:20'],
            'area_value' => ['nullable', 'numeric'],
            'areaValue' => ['nullable', 'numeric'],
            'area_unit' => ['nullable', 'string', 'max:30'],
            'areaUnit' => ['nullable', 'string', 'max:30'],
            'furnishing' => ['nullable', 'string', 'max:30'],
            'amenities' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contactPhone' => ['nullable', 'string', 'max:20'],
            'photos' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
        ]);

        $assetType = $validated['asset_type'] ?? $validated['assetType'] ?? 'Property';
        $subType = $validated['property_sub_type'] ?? $validated['subType'] ?? 'Apartment / Flat';
        $price = $validated['price'];
        $priceDisplay = $validated['price_display'] ?? $validated['priceDisplay'] ?? ('₹ ' . number_format($price));
        $contactPhone = $validated['contact_phone'] ?? $validated['contactPhone'] ?? $user->phone;

        DB::beginTransaction();
        try {
            $property = Property::create([
                'user_id' => $user->id,
                'posted_by_role' => $user->role === 'builder' ? 'Builder' : ($user->role === 'agent' ? 'Agent' : 'Owner'),
                'intent' => $validated['intent'],
                'asset_type' => $assetType,
                'property_sub_type' => $subType,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'price' => $price,
                'price_display' => $priceDisplay,
                'society_or_yard' => $validated['society_or_yard'] ?? $validated['society'] ?? null,
                'locality' => $validated['locality'],
                'city' => $validated['city'],
                'state' => $validated['state'] ?? 'Gujarat',
                'pincode' => $validated['pincode'] ?? null,
                'bhk' => $validated['bhk'] ?? null,
                'bathrooms' => $validated['bathrooms'] ?? null,
                'balconies' => $validated['balconies'] ?? null,
                'area_value' => $validated['area_value'] ?? $validated['areaValue'] ?? null,
                'area_unit' => $validated['area_unit'] ?? $validated['areaUnit'] ?? 'Sq.Ft',
                'furnishing' => $validated['furnishing'] ?? null,
                'amenities' => $validated['amenities'] ?? [],
                'contact_phone' => $contactPhone,
                'status' => 'Under Review',
            ]);

            // Save photos
            $photos = $validated['photos'] ?? $validated['images'] ?? [];
            foreach ($photos as $index => $photo) {
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_url' => $photo['url'] ?? $photo['image_url'],
                    'thumbnail_url' => $photo['thumbnail_url'] ?? $photo['url'] ?? $photo['image_url'],
                    'label' => $photo['label'] ?? 'Photo ' . ($index + 1),
                    'sort_order' => $index + 1,
                    'is_cover' => $photo['isCover'] ?? $photo['is_cover'] ?? ($index === 0),
                ]);
            }

            DB::commit();

            return $this->successResponse(
                $property->load('images'),
                'Property submitted successfully! It is currently under review by admin.',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to submit property: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Upload Image Endpoint
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'], // 10MB max
        ]);

        $file = $request->file('image');
        $filename = 'prop_' . Str::random(20) . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('properties', $filename, 'public');

        $url = asset('storage/' . $path);

        return $this->successResponse([
            'image_url' => $url,
            'thumbnail_url' => $url,
            'file_name' => $filename,
        ], 'Image uploaded successfully', 201);
    }

    /**
     * My Listings
     */
    public function myListings(Request $request): JsonResponse
    {
        $properties = Property::with(['images', 'coverImage'])
            ->where('user_id', $request->user()->id)
            ->where('is_deleted', false)
            ->latest()
            ->get();

        return $this->successResponse($properties, 'User listings retrieved successfully');
    }

    /**
     * Delete Property Listing
     */
    public function destroy(int $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($id);

        if ($property->user_id !== $user->id && $user->role !== 'admin') {
            return $this->errorResponse('Unauthorized to delete this property', 403);
        }

        $property->update(['is_deleted' => true]);
        $property->delete();

        return $this->successResponse(null, 'Property listing deleted successfully');
    }

    /**
     * Format property and enforce contact privacy mask
     */
    private function formatPropertyResponse(Property $property, bool $isUnlocked, bool $isSaved): array
    {
        $arr = $property->toArray();
        $arr['is_contact_unlocked'] = $isUnlocked;
        $arr['is_saved'] = $isSaved;

        if (!$isUnlocked) {
            $phone = $property->contact_phone;
            $arr['contact_phone'] = substr($phone, 0, 7) . ' XXXXX';
            $arr['is_phone_masked'] = true;
        } else {
            $arr['is_phone_masked'] = false;
        }

        return $arr;
    }
}
