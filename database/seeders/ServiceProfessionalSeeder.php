<?php

namespace Database\Seeders;

use App\Models\ServiceProfessional;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ServiceProfessionalSeeder extends Seeder
{
    public function run(): void
    {
        $lawyerUser = User::firstOrCreate(
            ['email' => 'lawyer@propertywala.com'],
            [
                'name' => 'Adv. Rajesh Mehta',
                'phone' => '+91 98250 11223',
                'password' => Hash::make('secret123'),
                'role' => 'professional',
                'city' => 'Surat',
                'is_phone_verified' => true
            ]
        );

        $bankerUser = User::firstOrCreate(
            ['email' => 'banker@propertywala.com'],
            [
                'name' => 'Hitesh Patel (HDFC Loan Specialist)',
                'phone' => '+91 98251 33445',
                'password' => Hash::make('secret123'),
                'role' => 'professional',
                'city' => 'Surat',
                'is_phone_verified' => true
            ]
        );

        $designerUser = User::firstOrCreate(
            ['email' => 'designer@propertywala.com'],
            [
                'name' => 'Pooja Shah Studio',
                'phone' => '+91 98252 55667',
                'password' => Hash::make('secret123'),
                'role' => 'professional',
                'city' => 'Surat',
                'is_phone_verified' => true
            ]
        );

        $professionals = [
            [
                'user_id' => $lawyerUser->id,
                'category' => 'Legal Title Verification',
                'name' => 'Adv. Rajesh Mehta & Associates',
                'firm_name' => 'Mehta Legal Chambers',
                'experience_years' => 15,
                'experience_display' => '15+ Years in High Court & District Registrar',
                'phone' => '+91 98250 11223',
                'email' => 'rajesh.mehta@example.com',
                'city' => 'Surat',
                'specializations' => ['Title Search Report (30 Years)', 'Sale Deed Registration', '7/12 & Index-2 Verification', 'SARFAESI Clearance'],
                'description' => 'Specialized in real estate title search, encumbrance verification, and document drafting across Surat and Gujarat.',
                'license_or_reg_number' => 'G/1042/2009',
                'status' => 'Approved',
                'rating' => 4.9,
                'verified_deals_count' => 128
            ],
            [
                'user_id' => $bankerUser->id,
                'category' => 'Banker (for Loan)',
                'name' => 'Hitesh Patel',
                'firm_name' => 'Authorized Channel Partner - HDFC & SBI Bank',
                'experience_years' => 9,
                'experience_display' => '9+ Years in Retail & Commercial Mortgages',
                'phone' => '+91 98251 33445',
                'email' => 'hitesh.loans@example.com',
                'city' => 'Surat',
                'specializations' => ['Home Loan Fast Sanction', 'Balance Transfer with Top-Up', 'Commercial Plot Loans', 'SARFAESI Auction Funding'],
                'description' => 'Assisting home buyers with lowest interest rates, minimal paperwork, and doorstep document pickup across Surat.',
                'license_or_reg_number' => 'DSA-HDFC-9021',
                'status' => 'Approved',
                'rating' => 4.8,
                'verified_deals_count' => 310
            ],
            [
                'user_id' => $designerUser->id,
                'category' => 'Interior & Vastu',
                'name' => 'Pooja Shah',
                'firm_name' => 'VastuSpaze Architectural Interiors',
                'experience_years' => 8,
                'experience_display' => '8+ Years & 80+ Luxury Turnkey Homes Delivered',
                'phone' => '+91 98252 55667',
                'email' => 'pooja.interior@example.com',
                'city' => 'Surat',
                'specializations' => ['Vastu Compliant Layouts', 'Turnkey 3BHK/4BHK Interiors', 'Modular Kitchen & Wardrobes', '3D Visual Walkthroughs'],
                'description' => 'Complete interior designing with authentic Vedic Vastu alignment for luxury apartments and bungalows.',
                'license_or_reg_number' => 'IIID-WZ-4421',
                'status' => 'Approved',
                'rating' => 5.0,
                'verified_deals_count' => 85
            ]
        ];

        foreach ($professionals as $p) {
            ServiceProfessional::updateOrCreate(
                ['user_id' => $p['user_id'], 'category' => $p['category']],
                $p
            );
        }
    }
}

