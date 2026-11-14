<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BankAuctionController;
use App\Http\Controllers\Api\DiscoveryController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProMembershipController;
use App\Http\Controllers\Api\PropertyController;
use App\Http\Controllers\Api\SavedPropertyController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\SystemController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Propertywala Mobile API v1 Routes (Complete 35 Endpoints Contract)
|--------------------------------------------------------------------------
*/

// Health Check for Railway deployment
Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => 'Propertywala API',
        'version' => '1.0.0',
        'timestamp' => now()->toISOString(),
    ]);
});

Route::prefix('v1')->group(function () {

    // 1. Authentication & Session Module (Mobile + Password, No OTP)
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
            Route::get('/profile', [AuthController::class, 'profile']);
            Route::put('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/logout', [AuthController::class, 'logout']);
        });
    });

    // 2. Discovery Module
    Route::prefix('discovery')->group(function () {
        Route::get('/cities', [DiscoveryController::class, 'cities']);
        Route::get('/new-projects', [DiscoveryController::class, 'newProjects']);
        Route::get('/config', [DiscoveryController::class, 'systemConfig']);
    });

    // 3. Properties & Search Engine Module
    Route::prefix('properties')->group(function () {
        Route::get('/', [PropertyController::class, 'index']);
        Route::get('/by-code/{code}', [PropertyController::class, 'getByCode']);
        Route::get('/{id}', [PropertyController::class, 'show'])->whereNumber('id');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/', [PropertyController::class, 'store']);
            Route::post('/upload-image', [PropertyController::class, 'uploadImage']);
            Route::get('/my/listings', [PropertyController::class, 'myListings']);
            Route::get('/my-properties', [PropertyController::class, 'myListings']);
            Route::delete('/{id}', [PropertyController::class, 'destroy'])->whereNumber('id');
            Route::post('/{id}/save', [SavedPropertyController::class, 'toggle'])->whereNumber('id');
        });
    });

    // 4. Bank Auctions & SARFAESI Seized Assets Module
    Route::prefix('bank-auctions')->group(function () {
        Route::get('/', [BankAuctionController::class, 'index']);
        Route::get('/vehicles', [BankAuctionController::class, 'vehicles']);
        Route::get('/{id}', [BankAuctionController::class, 'show'])->whereNumber('id');
    });

    // 5. In-App Buyer Lead Forwarding Module
    Route::prefix('leads')->group(function () {
        Route::post('/', [LeadController::class, 'store']);
        Route::post('/contact-seller', [LeadController::class, 'store']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/my-seller-leads', [LeadController::class, 'mySellerLeads']);
            Route::get('/property/{propertyId}', [LeadController::class, 'propertyLeads'])->whereNumber('propertyId');
        });
    });

    // 6. Saved / Bookmarked Properties Module
    Route::prefix('saved')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [SavedPropertyController::class, 'index']);
        Route::post('/toggle/{propertyId}', [SavedPropertyController::class, 'toggle'])->whereNumber('propertyId');
    });

    // 7. Pro Membership & Contact Unlocks Module
    Route::prefix('pro')->group(function () {
        Route::get('/plans', [ProMembershipController::class, 'plans']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/status', [ProMembershipController::class, 'status']);
            Route::post('/create-order', [ProMembershipController::class, 'createOrder']);
            Route::post('/verify-payment', [ProMembershipController::class, 'verifyPayment']);
            Route::post('/unlock-contact', [ProMembershipController::class, 'unlockContact']);
            Route::post('/unlock/{propertyId}', [ProMembershipController::class, 'unlockContact'])->whereNumber('propertyId');
        });
    });

    // 8. Professional Services Module
    Route::prefix('services')->group(function () {
        Route::get('/professionals', [ServiceController::class, 'professionals']);
        Route::get('/professionals/{category}', [ServiceController::class, 'professionals']);
        Route::post('/request-callback', [ServiceController::class, 'requestCallback']);
        Route::post('/inquire', [ServiceController::class, 'requestCallback']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/register', [ServiceController::class, 'register']);
            Route::get('/my-requests', [ServiceController::class, 'myRequests']);
        });
    });

    // 9. Notifications Module
    Route::prefix('notifications')->middleware('auth:sanctum')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllRead']);
        Route::delete('/clear-all', [NotificationController::class, 'clearAll']);
    });

    // 10. Community Safety, Moderation & App Feedback
    Route::post('/moderation/report-property', [SystemController::class, 'reportProperty']);
    Route::post('/system/app-feedback', [SystemController::class, 'appFeedback']);
    Route::get('/system/config', [SystemController::class, 'config']);

});
