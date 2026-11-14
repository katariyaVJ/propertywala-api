<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppFeedbackReview;
use App\Models\PropertyReport;
use App\Models\SystemConfig;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    use ApiResponse;

    /**
     * Submit Property Fraud or Spam Report
     */
    public function reportProperty(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'property_id' => ['nullable', 'exists:properties,id'],
            'propertyId' => ['nullable', 'exists:properties,id'],
            'reason' => ['required', 'string', 'max:100'],
            'details' => ['nullable', 'string'],
        ]);

        $user = $request->user('sanctum');
        $propId = $validated['property_id'] ?? $validated['propertyId'];

        $report = PropertyReport::create([
            'property_id' => $propId,
            'reporter_user_id' => $user ? $user->id : null,
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'status' => 'Pending',
        ]);

        return $this->successResponse(
            $report,
            'Thank you for reporting. Our moderation team will investigate this listing immediately.',
            201
        );
    }

    /**
     * Submit In-App Feedback & Rating
     */
    public function appFeedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'feedback_text' => ['nullable', 'string'],
            'feedbackText' => ['nullable', 'string'],
            'app_version' => ['nullable', 'string', 'max:20'],
            'appVersion' => ['nullable', 'string', 'max:20'],
            'platform' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user('sanctum');

        $feedback = AppFeedbackReview::create([
            'user_id' => $user ? $user->id : null,
            'user_phone' => $user ? $user->phone : null,
            'rating' => $validated['rating'],
            'feedback_text' => $validated['feedback_text'] ?? $validated['feedbackText'] ?? null,
            'app_version' => $validated['app_version'] ?? $validated['appVersion'] ?? '1.0.0',
            'platform' => $validated['platform'] ?? 'Android',
        ]);

        return $this->successResponse(
            $feedback,
            'Thank you for your valuable feedback!',
            201
        );
    }

    /**
     * Get System Configuration (Helpline, WhatsApp, Legal URLs)
     */
    public function config(): JsonResponse
    {
        $configs = SystemConfig::pluck('config_value', 'config_key');

        return $this->successResponse($configs, 'Configuration retrieved successfully');
    }
}

