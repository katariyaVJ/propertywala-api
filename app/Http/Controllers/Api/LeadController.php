<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BuyerLead;
use App\Models\Notification;
use App\Models\ProContactUnlock;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    use ApiResponse;

    /**
     * Submit an in-app inquiry lead to property seller
     */
    public function store(Request $request): JsonResponse
    {
        $propertyId = $request->input('property_id', $request->input('propertyId'));
        $buyerName = $request->input('buyer_name', $request->input('buyerName'));
        $buyerPhone = $request->input('buyer_phone', $request->input('buyerPhone'));

        if (!$propertyId || !$buyerName || !$buyerPhone) {
            return $this->errorResponse('Property ID, buyer name, and phone are required', 422);
        }

        $property = Property::findOrFail($propertyId);
        $buyerUser = $request->user('sanctum');

        $lead = BuyerLead::create([
            'property_id' => $property->id,
            'seller_user_id' => $property->user_id,
            'buyer_user_id' => $buyerUser ? $buyerUser->id : null,
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'buyer_email' => $request->input('buyer_email', $request->input('buyerEmail')),
            'intent' => $request->input('intent', 'Self Use'),
            'timeline' => $request->input('timeline', 'Immediately'),
            'source' => $request->input('source', 'Contact Seller'),
            'status' => 'New',
        ]);

        // Increment leads counter on property
        $property->increment('leads_count');

        // Send in-app notification to seller
        Notification::create([
            'user_id' => $property->user_id,
            'title' => 'New Buyer Lead Received!',
            'message' => "{$buyerName} ({$buyerPhone}) is interested in your property: {$property->title}",
            'category' => 'lead',
            'reference_type' => 'buyer_lead',
            'reference_id' => (string) $lead->id,
        ]);

        // Check if buyer has unlocked the contact
        $isUnlocked = false;
        if ($buyerUser) {
            $isUnlocked = ProContactUnlock::where('user_id', $buyerUser->id)->where('property_id', $property->id)->exists();
        }

        $sellerPhone = $isUnlocked ? $property->contact_phone : (substr($property->contact_phone, 0, 7) . ' XXXXX');

        return $this->successResponse([
            'lead_id' => $lead->id,
            'leadId' => $lead->id,
            'seller_contact' => [
                'name' => $property->user->name ?? 'Direct Owner',
                'role' => $property->posted_by_role,
                'phone' => $sellerPhone,
                'is_masked' => !$isUnlocked,
                'isMasked' => !$isUnlocked,
            ]
        ], 'Inquiry sent successfully to seller', 201);
    }

    /**
     * View all leads received across seller's listings
     */
    public function mySellerLeads(Request $request): JsonResponse
    {
        $user = $request->user();

        $leads = BuyerLead::with(['property:id,title,property_code,price_display,locality,city'])
            ->where('seller_user_id', $user->id)
            ->latest()
            ->paginate(20);

        return $this->successResponse(
            $leads->items(),
            'Seller leads retrieved successfully',
            200,
            [
                'current_page' => $leads->currentPage(),
                'last_page' => $leads->lastPage(),
                'total' => $leads->total(),
            ]
        );
    }

    /**
     * View leads for a specific property listing
     */
    public function propertyLeads(int $propertyId, Request $request): JsonResponse
    {
        $user = $request->user();
        $property = Property::findOrFail($propertyId);

        if ($property->user_id !== $user->id && $user->role !== 'admin') {
            return $this->errorResponse('Unauthorized to view leads for this property', 403);
        }

        $leads = BuyerLead::where('property_id', $propertyId)
            ->latest()
            ->get();

        return $this->successResponse($leads, 'Property leads retrieved successfully');
    }
}
