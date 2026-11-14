<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * User Registration (No OTP - Direct Mobile + Password Flow)
     * Fields: name, mobile/phone, password, confirm_password, optional email, optional city, role
     */
    public function register(Request $request): JsonResponse
    {
        // 1. Resolve mobile field (accepts either 'mobile' or 'phone')
        $rawMobile = $request->input('mobile', $request->input('phone'));
        $cleanMobile = $this->cleanPhoneNumber($rawMobile);

        $request->merge([
            'phone' => $cleanMobile,
            'mobile' => $cleanMobile,
        ]);

        // 2. Validation
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^[6-9]\d{9}$/', 'unique:users,phone'],
            'email' => ['nullable', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'confirm_password' => ['nullable', 'string', 'same:password'],
            'password_confirmation' => ['nullable', 'string', 'same:password'],
            'confirm_pass' => ['nullable', 'string', 'same:password'],
            'city' => ['nullable', 'string', 'max:80'],
            'role' => ['nullable', 'string', 'max:50'],
        ], [
            'name.required' => 'Full name is compulsory.',
            'name.min' => 'Full name must be at least 3 characters.',
            'phone.required' => 'Mobile number is compulsory.',
            'phone.regex' => 'Please enter a valid 10-digit mobile number.',
            'phone.unique' => 'This mobile number is already registered. Please login instead.',
            'password.required' => 'Password is compulsory.',
            'password.min' => 'Password must be at least 8 characters.',
            'confirm_password.same' => 'Confirm password does not match.',
            'confirm_pass.same' => 'Confirm password does not match.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
        ]);

        // 3. Normalize Role
        $role = $validated['role'] ?? 'Buyer / Tenant';

        // 4. Create User Record
        $user = User::create([
            'name' => trim($validated['name']),
            'phone' => $cleanMobile,
            'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
            'password' => Hash::make($validated['password']),
            'city' => $validated['city'] ?? 'Surat',
            'role' => $role,
            'notification_updates' => true,
            'is_phone_verified' => true,
        ]);

        $token = $user->createToken('flutter-app')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'alt_phone' => $user->alt_phone,
                'city' => $user->city,
                'role' => $user->role,
                'notification_updates' => $user->notification_updates,
                'is_pro_member' => false,
            ],
            'tokens' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]
        ], 'Account created successfully! Welcome to Propertywala.', 201);
    }

    /**
     * User Login with Mobile Number and Password (No OTP)
     */
    public function login(Request $request): JsonResponse
    {
        $rawMobile = $request->input('mobile', $request->input('phone'));
        $cleanMobile = $this->cleanPhoneNumber($rawMobile);

        $request->validate([
            'password' => ['required', 'string'],
        ], [
            'password.required' => 'Password is required.',
        ]);

        if (empty($cleanMobile)) {
            return $this->errorResponse('Please enter your 10-digit mobile number.', 422);
        }

        // Search user by 10-digit mobile or variations
        $user = User::where('phone', $cleanMobile)
            ->orWhere('phone', '+91 ' . $cleanMobile)
            ->orWhere('phone', '+91' . $cleanMobile)
            ->orWhere('phone', 'LIKE', '%' . $cleanMobile)
            ->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return $this->errorResponse('Login failed! Invalid mobile number or password.', 401);
        }

        if ($user->is_blocked) {
            return $this->errorResponse('Your account has been suspended. Please contact support.', 403);
        }

        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('flutter-app')->plainTextToken;
        $activePro = $user->proMemberships()
            ->where('status', 'Active')
            ->where('valid_until', '>', now())
            ->first();

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'alt_phone' => $user->alt_phone,
                'city' => $user->city,
                'role' => $user->role,
                'notification_updates' => (bool) $user->notification_updates,
                'is_pro_member' => $activePro !== null,
            ],
            'tokens' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            'pro_membership' => $activePro ? [
                'plan_name' => $activePro->plan_name,
                'remaining_contacts' => $activePro->remainingContacts(),
                'valid_until' => $activePro->valid_until->toIso8601String(),
            ] : null,
        ], 'Login successful! Welcome back to Propertywala.');
    }

    /**
     * Get Current Authenticated Profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        $activePro = $user->proMemberships()
            ->where('status', 'Active')
            ->where('valid_until', '>', now())
            ->first();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'alt_phone' => $user->alt_phone,
            'city' => $user->city,
            'role' => $user->role,
            'notification_updates' => (bool) $user->notification_updates,
            'is_pro_member' => $activePro !== null,
            'pro_membership' => $activePro ? [
                'plan_name' => $activePro->plan_name,
                'contact_limit' => $activePro->contact_limit,
                'contacts_used' => $activePro->contacts_used,
                'remaining_contacts' => $activePro->remainingContacts(),
                'valid_until' => $activePro->valid_until->toIso8601String(),
            ] : null,
            'stats' => [
                'posted_listings_count' => $user->properties()->count(),
                'saved_properties_count' => $user->savedProperties()->count(),
            ]
        ], 'Profile retrieved successfully');
    }

    /**
     * Update Profile (Store Email ID, Alt Phone, Name, City, Role, Notification Updates)
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'min:3', 'max:120'],
            'email' => ['sometimes', 'nullable', 'email', 'max:160', 'unique:users,email,' . $user->id],
            'alt_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'altPhone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'city' => ['sometimes', 'string', 'max:80'],
            'role' => ['sometimes', 'string', 'max:50'],
            'notification_updates' => ['sometimes', 'boolean'],
            'notificationUpdates' => ['sometimes', 'boolean'],
        ]);

        $updateData = [];

        if (isset($validated['name'])) {
            $updateData['name'] = trim($validated['name']);
        }

        // Store Email if user enters email ID in profile screen
        if (array_key_exists('email', $validated)) {
            $updateData['email'] = !empty($validated['email']) ? strtolower(trim($validated['email'])) : null;
        }

        // Store Alternate Phone
        $alt = $validated['alt_phone'] ?? $validated['altPhone'] ?? null;
        if ($alt !== null) {
            $updateData['alt_phone'] = $alt;
        }

        if (isset($validated['city'])) {
            $updateData['city'] = $validated['city'];
        }

        if (isset($validated['role'])) {
            $updateData['role'] = $validated['role'];
        }

        if (isset($validated['notification_updates']) || isset($validated['notificationUpdates'])) {
            $updateData['notification_updates'] = $validated['notification_updates'] ?? $validated['notificationUpdates'];
        }

        $user->update($updateData);

        return $this->successResponse([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'phone' => $user->phone,
                'email' => $user->email,
                'alt_phone' => $user->alt_phone,
                'city' => $user->city,
                'role' => $user->role,
                'notification_updates' => (bool) $user->notification_updates,
            ]
        ], 'Profile updated successfully');
    }

    /**
     * Refresh Token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();
        $newToken = $user->createToken('flutter-app')->plainTextToken;

        return $this->successResponse([
            'access_token' => $newToken,
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Helper to clean Indian mobile number into 10 digits
     */
    private function cleanPhoneNumber(?string $phone): string
    {
        if (!$phone) return '';
        $digits = preg_replace('/\D/', '', $phone);
        // If starts with 91 and has 12 digits, extract last 10
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }
        if (strlen($digits) > 10) {
            return substr($digits, -10);
        }
        return $digits;
    }
}
