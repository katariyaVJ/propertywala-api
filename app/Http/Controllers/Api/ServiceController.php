<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServiceInquiry;
use App\Models\ServiceProfessional;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ServiceController extends Controller
{
    use ApiResponse;

    /**
     * Browse verified service professionals (Legal, Loan, Interior)
     */
    public function professionals(Request $request, ?string $category = null): JsonResponse
    {
        $query = ServiceProfessional::approved();

        $cat = $category ?? $request->query('category');
        if ($cat) {
            $query->where('category', $cat);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->query('city'));
        }

        $professionals = $query->orderBy('rating', 'desc')->get();

        return $this->successResponse($professionals, 'Professionals retrieved successfully');
    }

    /**
     * Register as a Service Professional
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['nullable', 'string', 'max:80'],
            'serviceCategory' => ['nullable', 'string', 'max:80'],
            'name' => ['required', 'string', 'max:120'],
            'firm_name' => ['nullable', 'string', 'max:150'],
            'firmName' => ['nullable', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:160'],
            'city' => ['required', 'string', 'max:80'],
            'experience_years' => ['nullable', 'integer'],
            'experienceYears' => ['nullable', 'integer'],
            'specializations' => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
            'license_or_reg_number' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user('sanctum');
        $phone = $validated['phone'];

        if (!$user) {
            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? ('pro_' . substr(preg_replace('/[^0-9]/', '', $phone), -6) . '@propertywala.app'),
                    'password' => Hash::make('secret123'),
                    'role' => 'agent',
                    'city' => $validated['city'],
                    'is_phone_verified' => true,
                ]
            );
        }

        $professional = ServiceProfessional::create([
            'user_id' => $user->id,
            'category' => $validated['category'] ?? $validated['serviceCategory'] ?? 'Legal Title Verification',
            'name' => $validated['name'],
            'firm_name' => $validated['firm_name'] ?? $validated['firmName'] ?? $validated['name'],
            'experience_years' => $validated['experience_years'] ?? $validated['experienceYears'] ?? 3,
            'experience_display' => ($validated['experience_years'] ?? $validated['experienceYears'] ?? 3) . '+ Years Experience',
            'phone' => $phone,
            'email' => $validated['email'] ?? $user->email,
            'city' => $validated['city'],
            'specializations' => $validated['specializations'] ?? [],
            'description' => $validated['description'] ?? null,
            'license_or_reg_number' => $validated['license_or_reg_number'] ?? null,
            'status' => 'Pending',
        ]);

        return $this->successResponse(
            $professional,
            'Professional profile submitted successfully! It is under verification by admin.',
            201
        );
    }

    /**
     * Check user's own service registrations
     */
    public function myRequests(Request $request): JsonResponse
    {
        $user = $request->user('sanctum');
        $phone = $request->query('phone') ?? $request->query('mobile');

        $query = ServiceProfessional::latest();

        if ($user) {
            $query->where('user_id', $user->id);
        } elseif ($phone) {
            $clean = preg_replace('/[^0-9]/', '', $phone);
            $query->where('phone', 'LIKE', "%{$clean}%");
        } else {
            return $this->successResponse([], 'Service registration requests retrieved successfully');
        }

        $requests = $query->get();

        return $this->successResponse($requests, 'Service registration requests retrieved successfully');
    }

    /**
     * Submit callback inquiry for consultation or home loan
     */
    public function requestCallback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'professional_id' => ['nullable', 'exists:service_professionals,id'],
            'professionalId' => ['nullable', 'exists:service_professionals,id'],
            'user_name' => ['nullable', 'string', 'max:120'],
            'userName' => ['nullable', 'string', 'max:120'],
            'user_phone' => ['nullable', 'string', 'max:20'],
            'userPhone' => ['nullable', 'string', 'max:20'],
            'bank_name' => ['nullable', 'string', 'max:80'],
            'bankName' => ['nullable', 'string', 'max:80'],
            'loan_amount_requested' => ['nullable', 'numeric'],
            'loanAmountRequested' => ['nullable', 'numeric'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user('sanctum');
        $profId = $validated['professional_id'] ?? $validated['professionalId'];
        if (!$profId) {
            // Pick first approved professional in category if generic callback
            $profId = ServiceProfessional::approved()->value('id') ?? 1;
        }

        $inquiry = ServiceInquiry::create([
            'professional_id' => $profId,
            'user_id' => $user ? $user->id : 1,
            'user_name' => $validated['user_name'] ?? $validated['userName'] ?? ($user->name ?? 'Guest User'),
            'user_phone' => $validated['user_phone'] ?? $validated['userPhone'] ?? ($user->phone ?? '9876543210'),
            'bank_name' => $validated['bank_name'] ?? $validated['bankName'] ?? null,
            'loan_amount_requested' => $validated['loan_amount_requested'] ?? $validated['loanAmountRequested'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'Pending',
        ]);

        return $this->successResponse(
            $inquiry,
            'Consultation callback request submitted successfully. The expert will contact you shortly!',
            201
        );
    }
}
