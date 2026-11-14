<?php

namespace Database\Seeders;

use App\Models\NewProject;
use App\Models\ProjectUnit;
use Illuminate\Database\Seeder;

class NewProjectSeeder extends Seeder
{
    public function run(): void
    {
        $project1 = NewProject::updateOrCreate(
            ['rera_number' => 'PR/GJ/SURAT/SURAT-CITY/SUDA/RAA09876/010126'],
            [
                'name' => 'Rajhans Synfonia & Sky Villas',
                'developer_name' => 'Rajhans (Desai-Jain) Group',
                'developer_stats' => '25+ Years Experience | 40+ Delivered Projects',
                'locality' => 'Vesu',
                'city' => 'Surat',
                'price_range' => '₹ 1.25 Cr - 2.80 Cr',
                'bhk_options' => '3, 4 & 5 BHK Sky Villas',
                'possession' => 'Dec 2027',
                'construction_stage' => 'Under Construction (7th Slab Casted)',
                'total_units' => '160 Units across 4 Towers',
                'tags' => ['RERA Approved', 'Luxury Club House', '3 Side Open', 'Olympic Pool'],
                'amenities' => ['Infinity Swimming Pool', 'Mini Theater', 'EV Charging Station', 'Badminton Court', 'Kids Play Zone', 'Banquet Hall'],
                'description' => 'Ultra luxury residential enclave on main Vesu Canal corridor with world class amenities and 11ft ceiling heights.',
                'banner_gradient' => 'linear-gradient(135deg, #0d3b66, #001233)',
                'brochure_url' => 'https://propertywala.com/brochures/rajhans-synfonia.pdf',
                'is_featured' => true
            ]
        );

        ProjectUnit::updateOrCreate(
            ['project_id' => $project1->id, 'unit_type' => '3 BHK Luxury'],
            ['carpet_area_sqft' => 1850, 'price_estimate' => '₹ 1.25 Cr onwards']
        );
        ProjectUnit::updateOrCreate(
            ['project_id' => $project1->id, 'unit_type' => '4 BHK Sky Villa'],
            ['carpet_area_sqft' => 2600, 'price_estimate' => '₹ 1.95 Cr onwards']
        );
        ProjectUnit::updateOrCreate(
            ['project_id' => $project1->id, 'unit_type' => '5 BHK Penthouse'],
            ['carpet_area_sqft' => 3800, 'price_estimate' => '₹ 2.80 Cr onwards']
        );

        $project2 = NewProject::updateOrCreate(
            ['rera_number' => 'PR/GJ/SURAT/CHORASI/SUDA/RAA05432/150225'],
            [
                'name' => 'Green City Palm Greens',
                'developer_name' => 'Green City Developers',
                'developer_stats' => '18+ Years | 15+ Projects in Pal & Adajan',
                'locality' => 'Pal',
                'city' => 'Surat',
                'price_range' => '₹ 58.0 L - 95.0 L',
                'bhk_options' => '2 & 3 BHK Premium Flats',
                'possession' => 'June 2026',
                'construction_stage' => 'Finishing & Internal Plaster',
                'total_units' => '240 Flats across 6 Towers',
                'tags' => ['Near Pal RTO', 'GMC Piped Gas', 'Allotted Car Parking'],
                'amenities' => ['Terrace Garden', 'Senior Citizen Sit-out', 'Gymnasium', '24x7 Security & CCTV'],
                'description' => 'Affordable elegance near Pal RTO with top tier finishes and seamless connectivity to Surat Hazira expressway.',
                'banner_gradient' => 'linear-gradient(135deg, #1b4332, #081c15)',
                'brochure_url' => 'https://propertywala.com/brochures/palm-greens.pdf',
                'is_featured' => true
            ]
        );

        ProjectUnit::updateOrCreate(
            ['project_id' => $project2->id, 'unit_type' => '2 BHK Comfort'],
            ['carpet_area_sqft' => 1100, 'price_estimate' => '₹ 58.0 Lacs']
        );
        ProjectUnit::updateOrCreate(
            ['project_id' => $project2->id, 'unit_type' => '3 BHK Royale'],
            ['carpet_area_sqft' => 1520, 'price_estimate' => '₹ 85.0 Lacs']
        );
    }
}

