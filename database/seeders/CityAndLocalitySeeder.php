<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\FamousLocality;
use Illuminate\Database\Seeder;

class CityAndLocalitySeeder extends Seeder
{
    public function run(): void
    {
        $cities = [
            [
                'name' => 'Surat',
                'state' => 'Gujarat',
                'is_popular' => true,
                'display_order' => 1,
                'localities' => [
                    ['name' => 'Vesu', 'subtitle' => 'Premium High-rise Corridor', 'avg_price_per_sqft' => 6500.00, 'is_famous' => true],
                    ['name' => 'Adajan', 'subtitle' => 'Established Residential Hub', 'avg_price_per_sqft' => 5200.00, 'is_famous' => true],
                    ['name' => 'Pal', 'subtitle' => 'Near RTO & Canal Corridor', 'avg_price_per_sqft' => 4800.00, 'is_famous' => true],
                    ['name' => 'Dumas Road', 'subtitle' => 'Airport & Luxury Enclaves', 'avg_price_per_sqft' => 7200.00, 'is_famous' => true],
                    ['name' => 'Piplod', 'subtitle' => 'Commercial & Retail Centers', 'avg_price_per_sqft' => 6100.00, 'is_famous' => false],
                    ['name' => 'Althan', 'subtitle' => 'Rapidly Growing Residential Area', 'avg_price_per_sqft' => 4900.00, 'is_famous' => false],
                    ['name' => 'Varachha', 'subtitle' => 'Textile & Diamond District', 'avg_price_per_sqft' => 4500.00, 'is_famous' => false],
                    ['name' => 'Katargam', 'subtitle' => 'Diamond Bourse & Industry Hub', 'avg_price_per_sqft' => 4200.00, 'is_famous' => false],
                    ['name' => 'Jahangirpura', 'subtitle' => 'Green Suburban Enclave', 'avg_price_per_sqft' => 3800.00, 'is_famous' => false],
                ]
            ],
            [
                'name' => 'Ahmedabad',
                'state' => 'Gujarat',
                'is_popular' => true,
                'display_order' => 2,
                'localities' => [
                    ['name' => 'SG Highway', 'subtitle' => 'Corporate & IT Hub', 'avg_price_per_sqft' => 7500.00, 'is_famous' => true],
                    ['name' => 'Prahlad Nagar', 'subtitle' => 'Upscale Commercial & Residences', 'avg_price_per_sqft' => 8200.00, 'is_famous' => true],
                    ['name' => 'Bopal', 'subtitle' => 'Affordable & Premium High-rises', 'avg_price_per_sqft' => 5100.00, 'is_famous' => true],
                    ['name' => 'Satellite', 'subtitle' => 'Central Western Enclave', 'avg_price_per_sqft' => 6800.00, 'is_famous' => true],
                    ['name' => 'Bodakdev', 'subtitle' => 'Luxury Bungalows & Villas', 'avg_price_per_sqft' => 9500.00, 'is_famous' => false],
                ]
            ],
            [
                'name' => 'Vadodara',
                'state' => 'Gujarat',
                'is_popular' => true,
                'display_order' => 3,
                'localities' => [
                    ['name' => 'Alkapuri', 'subtitle' => 'Commercial Center', 'avg_price_per_sqft' => 6000.00, 'is_famous' => true],
                    ['name' => 'Vasna-Bhayli', 'subtitle' => 'Modern Residential Suburb', 'avg_price_per_sqft' => 4200.00, 'is_famous' => true],
                    ['name' => 'Gotri', 'subtitle' => 'Medical & University Area', 'avg_price_per_sqft' => 4600.00, 'is_famous' => true],
                ]
            ],
            [
                'name' => 'Rajkot',
                'state' => 'Gujarat',
                'is_popular' => true,
                'display_order' => 4,
                'localities' => [
                    ['name' => 'Kalawad Road', 'subtitle' => 'Prime Commercial & Living', 'avg_price_per_sqft' => 5400.00, 'is_famous' => true],
                    ['name' => '150 Feet Ring Road', 'subtitle' => 'Fastest Growing Arterial Belt', 'avg_price_per_sqft' => 4800.00, 'is_famous' => true],
                ]
            ],
            [
                'name' => 'Mumbai',
                'state' => 'Maharashtra',
                'is_popular' => true,
                'display_order' => 5,
                'localities' => [
                    ['name' => 'Andheri West', 'subtitle' => 'Metro & Media Hub', 'avg_price_per_sqft' => 24000.00, 'is_famous' => true],
                    ['name' => 'Bandra West', 'subtitle' => 'Celebrity & Ultra Luxury Homes', 'avg_price_per_sqft' => 45000.00, 'is_famous' => true],
                    ['name' => 'Powai', 'subtitle' => 'Lakeside Tech Hub', 'avg_price_per_sqft' => 21000.00, 'is_famous' => true],
                ]
            ]
        ];

        foreach ($cities as $cData) {
            $localities = $cData['localities'];
            unset($cData['localities']);

            $city = City::updateOrCreate(['name' => $cData['name']], $cData);

            foreach ($localities as $loc) {
                FamousLocality::updateOrCreate(
                    ['city_id' => $city->id, 'name' => $loc['name']],
                    [
                        'subtitle' => $loc['subtitle'],
                        'avg_price_per_sqft' => $loc['avg_price_per_sqft'],
                        'is_famous' => $loc['is_famous'],
                    ]
                );
            }
        }
    }
}
