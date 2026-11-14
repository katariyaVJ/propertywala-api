<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $sellerUser = User::firstOrCreate(
            ['email' => 'seller@propertywala.com'],
            [
                'name' => 'Ketan Sanghavi',
                'phone' => '+91 98251 88990',
                'password' => Hash::make('secret123'),
                'role' => 'owner',
                'city' => 'Surat',
                'is_phone_verified' => true
            ]
        );

        $bankerUser = User::firstOrCreate(
            ['email' => 'sbi.auction@propertywala.com'],
            [
                'name' => 'State Bank of India - Stressed Assets Recovery Branch',
                'phone' => '+91 98765 43210',
                'password' => Hash::make('secret123'),
                'role' => 'banker',
                'city' => 'Surat',
                'is_phone_verified' => true
            ]
        );

        // 1. Signature Property: Code '032' - 3 BHK Luxury in Vesu Surat
        $prop032 = Property::updateOrCreate(
            ['property_code' => '032'],
            [
                'user_id' => $sellerUser->id,
                'posted_by_role' => 'Owner',
                'intent' => 'Sell',
                'asset_type' => 'Property',
                'property_sub_type' => 'Apartment / Flat',
                'title' => '3 BHK Ultra Luxury Garden Facing Flat in Vesu',
                'description' => 'Direct from Owner! Fully furnished 3BHK flat on 8th floor with open garden views. Premium Italian marble flooring, false ceiling with ambient LEDs, modular kitchen with chimney, allotted basement parking, and clubhouse access.',
                'price' => 12500000.00,
                'price_display' => '₹ 1.25 Cr',
                'price_per_sqft' => '₹ 6,410 / sq.ft',
                'is_negotiable' => true,
                'society_or_yard' => 'Avadh Copper Stone',
                'locality' => 'Vesu',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395007',
                'latitude' => 21.1458,
                'longitude' => 72.7782,
                'bhk' => '3 BHK',
                'bathrooms' => '3 Bathrooms',
                'balconies' => '2 Balconies',
                'area_value' => 1950.00,
                'area_unit' => 'Sq.Ft',
                'area_type' => 'Super Built-up Area',
                'super_area_value' => 1950.00,
                'furnishing' => 'Fully Furnished',
                'amenities' => ['Clubhouse', 'Swimming Pool', 'Gymnasium', 'Children Play Area', 'Piped Gas', 'Covered Parking', '24x7 Security'],
                'is_bank_auction' => false,
                'contact_phone' => '+91 98251 88990',
                'status' => 'Active',
                'is_verified' => true,
                'is_featured' => true,
                'views_count' => 452,
                'leads_count' => 18,
            ]
        );

        $this->seedImages($prop032->id, [
            ['url' => 'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?auto=format&fit=crop&w=1200&q=80', 'label' => 'Hall / Living Room', 'is_cover' => true, 'order' => 1],
            ['url' => 'https://images.unsplash.com/photo-1600566753376-12c8ab7fb75b?auto=format&fit=crop&w=1200&q=80', 'label' => 'Master Bedroom', 'is_cover' => false, 'order' => 2],
            ['url' => 'https://images.unsplash.com/photo-1556911220-e15b29be8c8f?auto=format&fit=crop&w=1200&q=80', 'label' => 'Modular Kitchen', 'is_cover' => false, 'order' => 3],
            ['url' => 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750?auto=format&fit=crop&w=1200&q=80', 'label' => 'Balcony & Garden View', 'is_cover' => false, 'order' => 4],
        ]);

        // 2. Bank Auction SARFAESI Property: Code 'PW-AUCTION-101'
        $propAuction = Property::updateOrCreate(
            ['property_code' => 'PW-AUCTION-101'],
            [
                'user_id' => $bankerUser->id,
                'posted_by_role' => 'Banker',
                'intent' => 'Bank Auction',
                'asset_type' => 'Property',
                'property_sub_type' => 'Apartment / Flat',
                'title' => 'SBI SARFAESI Auction: 2 BHK Flat at Pal Gam, Surat',
                'description' => 'E-Auction Sale Notice under SARFAESI Act, 2002 for immovable property mortgaged to State Bank of India SARB branch Surat. Possession status: Physical Possession with Bank. Clear marketable title with zero encumbrances as per public notice.',
                'price' => 3800000.00,
                'price_display' => '₹ 38.0 Lacs',
                'price_per_sqft' => '₹ 3,450 / sq.ft',
                'is_negotiable' => false,
                'society_or_yard' => 'Shreeji Residency, Flat No. 402',
                'locality' => 'Pal',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395009',
                'latitude' => 21.1950,
                'longitude' => 72.7830,
                'bhk' => '2 BHK',
                'bathrooms' => '2 Bathrooms',
                'balconies' => '1 Balcony',
                'area_value' => 1100.00,
                'area_unit' => 'Sq.Ft',
                'area_type' => 'Carpet Area',
                'super_area_value' => 1250.00,
                'furnishing' => 'Unfurnished',
                'amenities' => ['Lift', '24x7 Security', 'Fire Fighting System', 'Open Parking'],
                'is_bank_auction' => true,
                'bank_name' => 'State Bank of India',
                'auction_date' => '25 Nov 2026 11:00 AM',
                'auction_timestamp' => Carbon::parse('2026-11-25 11:00:00'),
                'reserve_price' => 3800000.00,
                'emd_amount' => 380000.00,
                'borrower_name' => 'Shree Shyam Steels & M/s Patel Associates',
                'bank_branch_or_yard' => 'SBI SARB Branch, Ring Road, Surat',
                'notice_pdf_url' => 'https://propertywala.com/notices/sbi_pal_auction_notice.pdf',
                'auction_status' => 'Upcoming',
                'contact_phone' => '+91 98765 43210',
                'status' => 'Active',
                'is_verified' => true,
                'is_featured' => true,
                'views_count' => 890,
                'leads_count' => 42,
            ]
        );

        $this->seedImages($propAuction->id, [
            ['url' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?auto=format&fit=crop&w=1200&q=80', 'label' => 'Building Front Facade', 'is_cover' => true, 'order' => 1],
            ['url' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80', 'label' => 'Flat Interior Living Space', 'is_cover' => false, 'order' => 2],
            ['url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1200&q=80', 'label' => 'Balcony Street View', 'is_cover' => false, 'order' => 3],
        ]);

        // 3. Bank Seized Vehicle: Code 'PW-VEHICLE-201' - Dual Asset Category
        $propVehicle = Property::updateOrCreate(
            ['property_code' => 'PW-VEHICLE-201'],
            [
                'user_id' => $bankerUser->id,
                'posted_by_role' => 'Banker',
                'intent' => 'Bank Auction',
                'asset_type' => 'Vehicle',
                'property_sub_type' => 'Commercial Truck',
                'title' => 'Bank Seized Tata Signa 2823 Commercial Tipper Truck',
                'description' => 'Public Auction of Bank Seized Commercial Commercial Heavy Goods Vehicle. Seized under loan hypothecation clause. Parked at Sachin GIDC Bank Authorized Yard. Physical inspection open on all bank working days.',
                'price' => 1450000.00,
                'price_display' => '₹ 14.5 Lacs Reserve',
                'is_negotiable' => false,
                'society_or_yard' => 'Sachin GIDC Authorized Bank Seizure Yard',
                'locality' => 'Sachin',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '394230',
                'latitude' => 21.0850,
                'longitude' => 72.8620,
                'vehicle_category' => 'Commercial Truck',
                'vehicle_model' => 'Tata Signa 2823.K HD 9S Tipper',
                'vehicle_make_year' => 2022,
                'vehicle_km_driven' => 64500,
                'vehicle_owner_type' => 'First Owner (Hypothecated)',
                'vehicle_fuel_type' => 'Diesel',
                'vehicle_transmission' => 'Manual',
                'vehicle_reg_number' => 'GJ-05-BX-4921',
                'vehicle_inspection_features' => [
                    'Engine Condition: Running / Self Start Verified',
                    'Tyre Tread Depth: 70% Life Remaining across 10 Tyres',
                    'Hydraulic Tipper: Fully Functional',
                    'RC Book & Insurance: Physical RC available with Bank',
                    'Chassis & Cabin: Original Factory Paint, Minor Bumper Scratches'
                ],
                'is_bank_auction' => true,
                'bank_name' => 'Bank of Baroda',
                'auction_date' => '28 Nov 2026 02:30 PM',
                'auction_timestamp' => Carbon::parse('2026-11-28 14:30:00'),
                'reserve_price' => 1450000.00,
                'emd_amount' => 150000.00,
                'borrower_name' => 'Patidar Earthmovers & Transport Co.',
                'bank_branch_or_yard' => 'BOB Regional Recovery Cell, Ring Road Surat',
                'notice_pdf_url' => 'https://propertywala.com/notices/bob_truck_seizure_notice.pdf',
                'auction_status' => 'Upcoming',
                'contact_phone' => '+91 98765 43210',
                'status' => 'Active',
                'is_verified' => true,
                'is_featured' => true,
                'views_count' => 620,
                'leads_count' => 31,
            ]
        );

        $this->seedImages($propVehicle->id, [
            ['url' => 'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=1200&q=80', 'label' => 'Front Angle & Cabin View', 'is_cover' => true, 'order' => 1],
            ['url' => 'https://images.unsplash.com/photo-1519003722824-194d4455a60c?auto=format&fit=crop&w=1200&q=80', 'label' => 'Side Body & Tipper Hydraulic', 'is_cover' => false, 'order' => 2],
            ['url' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=1200&q=80', 'label' => 'Dashboard & Odometer Reading', 'is_cover' => false, 'order' => 3],
        ]);

        // 4. Resale Commercial Shop in Adajan: Code '045'
        $propShop = Property::updateOrCreate(
            ['property_code' => '045'],
            [
                'user_id' => $sellerUser->id,
                'posted_by_role' => 'Agent',
                'intent' => 'Sell',
                'asset_type' => 'Property',
                'property_sub_type' => 'Commercial Shop',
                'title' => 'Prime Ground Floor Main Road Corner Shop in Adajan',
                'description' => 'High footfall road touch commercial showroom space on prime Prime Adajan Honey Park road. Double height ceiling suitable for mezzanine floor, glass facade, high rental return potential (ROI 6.8%).',
                'price' => 6500000.00,
                'price_display' => '₹ 65.0 Lacs',
                'price_per_sqft' => '₹ 10,833 / sq.ft',
                'is_negotiable' => true,
                'society_or_yard' => 'Adajan Business Hub',
                'locality' => 'Adajan',
                'city' => 'Surat',
                'state' => 'Gujarat',
                'pincode' => '395009',
                'latitude' => 21.1980,
                'longitude' => 72.7950,
                'area_value' => 600.00,
                'area_unit' => 'Sq.Ft',
                'area_type' => 'Super Built-up Area',
                'super_area_value' => 600.00,
                'furnishing' => 'Semi-Furnished',
                'amenities' => ['Main Road Facing', 'Basement Parking', 'Power Backup', 'Water Connection', 'CCTV'],
                'is_bank_auction' => false,
                'contact_phone' => '+91 98251 88990',
                'status' => 'Active',
                'is_verified' => true,
                'is_featured' => false,
                'views_count' => 312,
                'leads_count' => 9,
            ]
        );

        $this->seedImages($propShop->id, [
            ['url' => 'https://images.unsplash.com/photo-1582037928769-181f2644ecb7?auto=format&fit=crop&w=1200&q=80', 'label' => 'Main Showroom Frontage', 'is_cover' => true, 'order' => 1],
            ['url' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80', 'label' => 'Internal High Ceiling Space', 'is_cover' => false, 'order' => 2],
        ]);
    }

    private function seedImages(int $propertyId, array $images): void
    {
        foreach ($images as $img) {
            PropertyImage::updateOrCreate(
                ['property_id' => $propertyId, 'sort_order' => $img['order']],
                [
                    'image_url' => $img['url'],
                    'thumbnail_url' => $img['url'],
                    'label' => $img['label'],
                    'is_cover' => $img['is_cover'],
                ]
            );
        }
    }
}

