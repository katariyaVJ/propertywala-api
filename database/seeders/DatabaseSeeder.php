<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin User for Filament Admin Panel
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Vijay Admin',
                'phone' => '+91 99999 99999',
                'password' => 'admin',
                'role' => 'admin',
                'city' => 'Surat',
                'is_phone_verified' => true,
            ]
        );

        // 2. Specialized System & Real Estate Seeders
        $this->call([
            CityAndLocalitySeeder::class,
            SystemConfigSeeder::class,
            ServiceProfessionalSeeder::class,
            NewProjectSeeder::class,
            PropertySeeder::class,
        ]);
    }
}
