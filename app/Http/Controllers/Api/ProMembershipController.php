<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProContactUnlock;
use App\Models\ProMembership;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProMembershipController extends Controller
{
    use ApiResponse;

    /**
     * List available Pro Membership Plans
     */
    public function plans(): JsonResponse
    {
        return $this->successResponse([
            [
                'id' => 'starter',
                'name' => 'Starter Pro',
                'tier' => 'STARTER',
                'price' => 48,
                'currency' => 'INR',
                'contact_limit' => 3,
                'duration_days' => 7,
                'features' => [
                    '3 Direct Owner Contact Unlocks',
                    'Basic Property Details Access',
                    'Zero Brokerage Guidance'
                ]
            ],
            [
                'id' => 'standard_pro',
                'name' => 'Standard Pro (Most Popular)',
                'tier' => 'PRO',
                'price' => 499,
                'currency' => 'INR',
                'contact_limit' => 40,
                'duration_days' => 30,
                'features' => [
                    '40 Direct Owner Contact Unlocks',
                    'Instant In-App Alerts on Price Drops',
                    'Priority Bank Auction & Seized Vehicle Notices',
                    'Verified Title Search Assistance',
                    'Zero Brokerage Guarantee'
                ]
            ],
            [
                'id' => 'unlimited_vip',
                'name' => 'Unlimited VIP',
                'tier' => 'VIP',
                'price' => 1499,
                'currency' => 'INR',
                'contact_limit' => 200,
                'duration_days' => 90,
                'features' => [
                    '200 Direct Contact Unlocks',
                    'Dedicated Relationship Manager',
                    'Advance Bank Seized Vehicle Physical Yard Inspection',
                    'Free Legal Document Verification Consultation',
                    'All Standard Pro Features'
                ]
            ]
        ], 'Plans retrieved successfully');
    }

    /**
     * Current Pro status of logged in user
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();
        $membership = ProMembership::where('user_id', $user->id)
            ->where('status', 'Active')
            ->where('valid_until', '>', now())
            ->first();

        if (!$membership) {
            return $this->successResponse([
                'is_pro' => false,
                'isPro' => false,
                'membership' => null
            ], 'No active Pro membership found');
        }

        return $this->successResponse([
            'is_pro' => true,
            'isPro' => true,
            'plan_name' => $membership->plan_name,
            'contact_limit' => $membership->contact_limit,
            'contacts_used' => $membership->contacts_used,
            'remaining_contacts' => $membership->remainingContacts(),
            'valid_from' => $membership->valid_from->toIso8601String(),
            'valid_until' => $membership->valid_until->toIso8601String(),
        ], 'Active Pro membership found');
    }

    /**
     * Create Payment Order (Gateway Integration / Razorpay Order ID)
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'plan_name' => ['nullable', 'string'],
            'planName' => ['nullable', 'string'],
        ]);

        $planName = $request->input('plan_name', $request->input('planName', 'Standard Pro'));
        $price = str_contains($planName, 'VIP') ? 1499 : (str_contains($planName, 'Starter') ? 48 : 499);
        $orderId = 'order_' . Str::random(14);

        return $this->successResponse([
            'order_id' => $orderId,
            'orderId' => $orderId,
            'amount' => $price * 100, // paise
            'currency' => 'INR',
            'plan_name' => $planName,
        ], 'Payment order initialized successfully');
    }

    /**
     * Verify Payment and Activate Pro Membership
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => ['nullable', 'string'],
            'orderId' => ['nullable', 'string'],
            'payment_id' => ['nullable', 'string'],
            'paymentId' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $planName = $request->input('plan_name', $request->input('planName', 'Standard Pro'));

        $contactLimit = str_contains($planName, 'VIP') ? 200 : (str_contains($planName, 'Starter') ? 3 : 40);
        $durationDays = str_contains($planName, 'VIP') ? 90 : (str_contains($planName, 'Starter') ? 7 : 30);
        $pricePaid = str_contains($planName, 'VIP') ? 1499 : (str_contains($planName, 'Starter') ? 48 : 499);

        $membership = ProMembership::create([
            'user_id' => $user->id,
            'plan_name' => $planName,
            'plan_tier' => str_contains($planName, 'VIP') ? 'VIP' : 'PRO',
            'price_paid' => $pricePaid,
            'currency' => 'INR',
            'contact_limit' => $contactLimit,
            'contacts_used' => 0,
            'valid_from' => now(),
            'valid_until' => Carbon::now()->addDays($durationDays),
            'status' => 'Active',
            'payment_order_id' => $request->input('order_id', $request->input('orderId', 'order_mock')),
            'payment_id' => $request->input('payment_id', $request->input('paymentId', 'pay_mock')),
        ]);

        return $this->successResponse([
            'membership_id' => $membership->id,
            'plan_name' => $membership->plan_name,
            'contact_limit' => $membership->contact_limit,
            'valid_until' => $membership->valid_until->toIso8601String(),
        ], 'Pro membership activated successfully! Welcome to Pro.');
    }

    /**
     * Unlock seller contact details using 1 Pro credit
     */
    public function unlockContact(Request $request, ?int $propertyId = null): JsonResponse
    {
        $id = $propertyId ?? $request->input('property_id') ?? $request->input('propertyId');
        if (!$id) {
            return $this->errorResponse('Property ID is required', 422);
        }

        $user = $request->user();
        $property = Property::findOrFail($id);

        // 1. Check if already unlocked
        $existing = ProContactUnlock::where('user_id', $user->id)
            ->where('property_id', $property->id)
            ->first();

        if ($existing) {
            return $this->successResponse([
                'property_id' => $property->id,
                'propertyId' => $property->id,
                'seller_name' => $property->user->name ?? 'Property Owner',
                'sellerName' => $property->user->name ?? 'Property Owner',
                'unmasked_phone' => $existing->seller_phone_revealed,
                'unmaskedPhone' => $existing->seller_phone_revealed,
                'is_masked' => false,
                'isMasked' => false,
                'already_unlocked' => true,
                'alreadyUnlocked' => true,
            ], 'Contact details already unlocked previously.');
        }

        // 2. Fetch active membership with remaining quota
        $membership = ProMembership::where('user_id', $user->id)
            ->where('status', 'Active')
            ->where('valid_until', '>', now())
            ->whereColumn('contacts_used', '<', 'contact_limit')
            ->lockForUpdate()
            ->first();

        if (!$membership) {
            return $this->errorResponse(
                'You need an active Pro Membership with available contact quota to unlock direct seller contacts.',
                403,
                ['code' => 'PRO_MEMBERSHIP_REQUIRED']
            );
        }

        DB::beginTransaction();
        try {
            $membership->increment('contacts_used');
            $membership->increment('opt_lock_version');

            $unlock = ProContactUnlock::create([
                'user_id' => $user->id,
                'property_id' => $property->id,
                'membership_id' => $membership->id,
                'seller_phone_revealed' => $property->contact_phone,
            ]);

            DB::commit();

            return $this->successResponse([
                'property_id' => $property->id,
                'propertyId' => $property->id,
                'seller_name' => $property->user->name ?? 'Property Owner',
                'sellerName' => $property->user->name ?? 'Property Owner',
                'unmasked_phone' => $unlock->seller_phone_revealed,
                'unmaskedPhone' => $unlock->seller_phone_revealed,
                'is_masked' => false,
                'isMasked' => false,
                'unlocked_contacts_remaining' => $membership->fresh()->remainingContacts(),
                'unlockedContactsRemaining' => $membership->fresh()->remainingContacts(),
                'already_unlocked' => false,
                'alreadyUnlocked' => false,
            ], 'Contact unlocked successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to unlock contact: ' . $e->getMessage(), 500);
        }
    }
}
