<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProContactUnlock;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\SavedProperty;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            'super_area_value' => ['nullable', 'numeric'],
            'area_unit' => ['nullable', 'string', 'max:30'],
            'areaUnit' => ['nullable', 'string', 'max:30'],
            'area_type' => ['nullable', 'string', 'max:40'],
            'security_deposit' => ['nullable', 'numeric'],
            'maintenance_charges' => ['nullable', 'string', 'max:60'],
            'preferred_tenant' => ['nullable', 'string', 'max:60'],
            'plot_frontage' => ['nullable', 'string', 'max:60'],
            'boundary_wall' => ['nullable', 'string', 'max:30'],
            'furnishing' => ['nullable', 'string', 'max:30'],
            'amenities' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'contact_phone' => ['nullable', 'string', 'max:20'],
            'contactPhone' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'alt_contact_phone' => ['nullable', 'string', 'max:20'],
            'altContactPhone' => ['nullable', 'string', 'max:20'],
            'secondary_phone' => ['nullable', 'string', 'max:20'],
            'posted_by_name' => ['nullable', 'string', 'max:120'],
            'posted_by_role' => ['nullable', 'string', 'max:30'],
            'is_bank_auction' => ['nullable', 'boolean'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'auction_by' => ['nullable', 'string', 'max:60'],
            'bank_asset_id' => ['nullable', 'string', 'max:60'],
            'action_type' => ['nullable', 'string', 'max:60'],
            'auction_date' => ['nullable', 'string', 'max:60'],
            'emd_last_date' => ['nullable', 'string', 'max:80'],
            'possession_type' => ['nullable', 'string', 'max:60'],
            'reserve_price' => ['nullable', 'numeric'],
            'emd_amount' => ['nullable', 'numeric'],
            'borrower_name' => ['nullable', 'string', 'max:120'],
            'bank_branch_or_yard' => ['nullable', 'string', 'max:140'],
            'notice_pdf_url' => ['nullable', 'string', 'max:500'],
            'auction_status' => ['nullable', 'string', 'max:30'],
            'facing' => ['nullable', 'string', 'max:30'],
            'property_age' => ['nullable', 'string', 'max:40'],
            'vehicle_title' => ['nullable', 'string', 'max:220'],
            'vehicle_description' => ['nullable', 'string'],
            'vehicle_category' => ['nullable', 'string', 'max:40'],
            'vehicle_brand' => ['nullable', 'string', 'max:80'],
            'vehicle_model' => ['nullable', 'string', 'max:120'],
            'vehicle_variant' => ['nullable', 'string', 'max:60'],
            'body_type' => ['nullable', 'string', 'max:60'],
            'vehicle_color' => ['nullable', 'string', 'max:40'],
            'engine_number' => ['nullable', 'string', 'max:60'],
            'chassis_number' => ['nullable', 'string', 'max:60'],
            'engine_capacity_cc' => ['nullable', 'integer'],
            'plate_type' => ['nullable', 'string', 'max:40'],
            'rc_status' => ['nullable', 'string', 'max:60'],
            'insurance_status' => ['nullable', 'string', 'max:60'],
            'ac_working' => ['nullable', 'string', 'max:20'],
            'vehicle_make_year' => ['nullable', 'integer'],
            'vehicle_km_driven' => ['nullable', 'integer'],
            'vehicle_owner_type' => ['nullable', 'string', 'max:30'],
            'vehicle_fuel_type' => ['nullable', 'string', 'max:30'],
            'vehicle_transmission' => ['nullable', 'string', 'max:30'],
            'vehicle_reg_number' => ['nullable', 'string', 'max:30'],
            'photos' => ['nullable', 'array'],
            'images' => ['nullable', 'array'],
        ]);

        $user = $request->user('sanctum');
        $contactPhone = $validated['contact_phone'] ?? $validated['contactPhone'] ?? $validated['phone'] ?? ($user ? $user->phone : '9876543210');

        if (!$user) {
            $phone = $contactPhone;
            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $validated['posted_by_name'] ?? 'Property Owner',
                    'email' => 'user_' . substr(preg_replace('/[^0-9]/', '', $phone), -6) . '@propertywala.app',
                    'password' => Hash::make('secret123'),
                    'role' => 'owner',
                    'city' => $validated['city'] ?? 'Surat',
                    'is_phone_verified' => true,
                ]
            );
        }

        $assetType = $validated['asset_type'] ?? $validated['assetType'] ?? 'Property';
        $subType = $validated['property_sub_type'] ?? $validated['subType'] ?? 'Apartment / Flat';
        $price = $validated['price'];
        $priceDisplay = $validated['price_display'] ?? $validated['priceDisplay'] ?? ('₹ ' . number_format($price));
        $isBankAuction = (bool) ($validated['is_bank_auction'] ?? ($validated['intent'] === 'Bank Auction'));
        $postedByRole = $validated['posted_by_role']
            ?? ($isBankAuction ? 'Banker' : ($user->role === 'builder' ? 'Builder' : ($user->role === 'agent' ? 'Agent' : 'Owner')));

        // Auto-assign sequential 3-digit Property Code (e.g. 033, 034...)
        $maxNum = 32;
        $allCodes = Property::withTrashed()->pluck('property_code')->toArray();
        foreach ($allCodes as $c) {
            if (is_numeric($c) && (int) $c > $maxNum) {
                $maxNum = (int) $c;
            }
        }
        $assignedCode = str_pad($maxNum + 1, 3, '0', STR_PAD_LEFT);
        while (Property::withTrashed()->where('property_code', $assignedCode)->exists()) {
            $maxNum++;
            $assignedCode = str_pad($maxNum + 1, 3, '0', STR_PAD_LEFT);
        }

        DB::beginTransaction();
        try {
            $property = Property::create([
                'property_code' => $assignedCode,
                'user_id' => $user->id,
                'posted_by_role' => $postedByRole,
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
                'super_area_value' => $validated['super_area_value'] ?? null,
                'area_unit' => $validated['area_unit'] ?? $validated['areaUnit'] ?? 'Sq.Ft',
                'area_type' => $validated['area_type'] ?? null,
                'security_deposit' => $validated['security_deposit'] ?? null,
                'maintenance_charges' => $validated['maintenance_charges'] ?? null,
                'preferred_tenant' => $validated['preferred_tenant'] ?? null,
                'plot_frontage' => $validated['plot_frontage'] ?? null,
                'boundary_wall' => $validated['boundary_wall'] ?? null,
                'furnishing' => $validated['furnishing'] ?? null,
                'amenities' => $validated['amenities'] ?? [],
                'is_bank_auction' => $isBankAuction,
                'auction_by' => $isBankAuction ? ($validated['auction_by'] ?? 'Bank (SARFAESI)') : null,
                'bank_asset_id' => $validated['bank_asset_id'] ?? null,
                'action_type' => $validated['action_type'] ?? ($isBankAuction ? 'Under Hypothecation' : null),
                'bank_name' => $validated['bank_name'] ?? null,
                'auction_date' => $validated['auction_date'] ?? null,
                'reserve_price' => $validated['reserve_price'] ?? ($isBankAuction ? $price : null),
                'emd_amount' => $validated['emd_amount'] ?? null,
                'borrower_name' => $validated['borrower_name'] ?? null,
                'bank_branch_or_yard' => $validated['bank_branch_or_yard'] ?? null,
                'notice_pdf_url' => $validated['notice_pdf_url'] ?? null,
                'auction_status' => $isBankAuction ? ($validated['auction_status'] ?? 'Upcoming') : null,
                'emd_last_date' => $validated['emd_last_date'] ?? null,
                'possession_type' => $validated['possession_type'] ?? ($isBankAuction ? 'Physical Possession' : null),
                'facing' => $validated['facing'] ?? null,
                'property_age' => $validated['property_age'] ?? null,
                'vehicle_category' => $validated['vehicle_category'] ?? null,
                'vehicle_brand' => $validated['vehicle_brand'] ?? null,
                'vehicle_model' => $validated['vehicle_model'] ?? null,
                'vehicle_variant' => $validated['vehicle_variant'] ?? null,
                'body_type' => $validated['body_type'] ?? null,
                'vehicle_color' => $validated['vehicle_color'] ?? null,
                'vehicle_title' => $validated['vehicle_title'] ?? null,
                'vehicle_description' => $validated['vehicle_description'] ?? null,
                'engine_number' => $validated['engine_number'] ?? null,
                'chassis_number' => $validated['chassis_number'] ?? null,
                'engine_capacity_cc' => $validated['engine_capacity_cc'] ?? null,
                'plate_type' => $validated['plate_type'] ?? null,
                'rc_status' => $validated['rc_status'] ?? null,
                'insurance_status' => $validated['insurance_status'] ?? null,
                'ac_working' => $validated['ac_working'] ?? null,
                'vehicle_make_year' => $validated['vehicle_make_year'] ?? null,
                'vehicle_km_driven' => $validated['vehicle_km_driven'] ?? null,
                'vehicle_owner_type' => $validated['vehicle_owner_type'] ?? null,
                'vehicle_fuel_type' => $validated['vehicle_fuel_type'] ?? null,
                'vehicle_transmission' => $validated['vehicle_transmission'] ?? null,
                'vehicle_reg_number' => $validated['vehicle_reg_number'] ?? null,
                'contact_phone' => $contactPhone,
                'alt_contact_phone' => $validated['alt_contact_phone'] ?? $validated['altContactPhone'] ?? $validated['secondary_phone'] ?? null,
                'status' => 'Under Review',
                'is_verified' => false,
            ]);

            // Save photos
            $photos = $validated['photos'] ?? $validated['images'] ?? [];
            if (empty($photos)) {
                $photos = [
                    [
                        'image_url' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80',
                        'label' => 'Living Room',
                        'is_cover' => true,
                    ]
                ];
            }

            foreach ($photos as $index => $photo) {
                $imgUrl = $photo['url'] ?? $photo['image_url'] ?? $photo['image'] ?? 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?auto=format&fit=crop&w=800&q=80';
                PropertyImage::create([
                    'property_id' => $property->id,
                    'image_url' => $imgUrl,
                    'thumbnail_url' => $photo['thumbnail_url'] ?? $imgUrl,
                    'label' => $photo['label'] ?? $photo['name'] ?? ('Photo ' . ($index + 1)),
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
        $user = $request->user('sanctum');
        $phone = $request->query('phone') ?? $request->query('mobile') ?? $request->query('contact_phone');

        $query = Property::with(['images', 'coverImage'])
            ->where('is_deleted', false);

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($phone) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $query->where(function ($q) use ($cleanPhone, $phone) {
                $q->where('contact_phone', 'LIKE', "%{$cleanPhone}%")
                  ->orWhere('contact_phone', 'LIKE', "%{$phone}%");
            });
        } else {
            return $this->successResponse([], 'User listings retrieved successfully');
        }

        $properties = $query->latest()->get();

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
            if (!empty($property->alt_contact_phone)) {
                $arr['alt_contact_phone'] = substr($property->alt_contact_phone, 0, 7) . ' XXXXX';
            }
            $arr['is_phone_masked'] = true;
        } else {
            $arr['is_phone_masked'] = false;
        }

        return $arr;
    }
}
