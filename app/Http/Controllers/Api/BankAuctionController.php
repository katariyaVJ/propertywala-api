<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BankAuctionController extends Controller
{
    use ApiResponse;

    /**
     * List bank auction properties & assets
     */
    public function index(Request $request): JsonResponse
    {
        $query = Property::with(['images', 'coverImage'])
            ->where('is_bank_auction', true)
            ->active();

        // 1. Bank Name Filter
        $bank = $request->query('bank', $request->query('bank_name'));
        if ($bank) {
            $query->where('bank_name', 'LIKE', "%{$bank}%");
        }

        // 2. City Filter
        if ($request->filled('city')) {
            $query->where('city', $request->query('city'));
        }

        // 3. Category Filter
        $category = $request->query('category', $request->query('asset_type'));
        if ($category && $category !== 'All') {
            if ($category === 'Properties') {
                $query->where('asset_type', 'Property');
            } elseif ($category === 'Cars' || $category === 'Vehicles') {
                $query->where('asset_type', 'Vehicle');
            } elseif ($category === 'Other Vehicles') {
                $query->where('asset_type', 'Vehicle')->where('vehicle_category', '!=', 'Car / SUV');
            }
        }

        if ($request->filled('auction_status')) {
            $query->where('auction_status', $request->query('auction_status'));
        }

        $perPage = min((int) $request->query('limit', $request->query('per_page', 20)), 50);
        $auctions = $query->latest('auction_timestamp')->paginate($perPage);

        return $this->successResponse(
            $auctions->items(),
            'Bank auctions retrieved successfully',
            200,
            [
                'current_page' => $auctions->currentPage(),
                'last_page' => $auctions->lastPage(),
                'per_page' => $auctions->perPage(),
                'total' => $auctions->total(),
            ]
        );
    }

    /**
     * Dedicated Seized Vehicles listing
     */
    public function vehicles(Request $request): JsonResponse
    {
        $query = Property::with(['images', 'coverImage'])
            ->where('is_bank_auction', true)
            ->where('asset_type', 'Vehicle')
            ->active();

        if ($request->filled('city')) {
            $query->where('city', $request->query('city'));
        }

        if ($request->filled('vehicle_category')) {
            $query->where('vehicle_category', $request->query('vehicle_category'));
        }

        $vehicles = $query->latest('auction_timestamp')->get();

        return $this->successResponse($vehicles, 'Seized vehicles retrieved successfully');
    }

    /**
     * Auction Details with SARFAESI Disclaimers
     */
    public function show(int $id): JsonResponse
    {
        $auction = Property::with(['images'])->where('is_bank_auction', true)->findOrFail($id);
        $auction->increment('views_count');

        $data = $auction->toArray();
        $data['sarfaesi_disclaimer'] = 'All auction bids and EMD submissions are conducted directly via the respective nodal bank authorized e-auction platforms. Propertywala is an independent search and discovery facilitation tool.';

        return $this->successResponse($data, 'Auction details retrieved successfully');
    }
}
