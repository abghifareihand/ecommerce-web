<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call StoreSettingSeeder first to ensure store data is always present
        $this->call(StoreSettingSeeder::class);

        // 1 Admin user for local development
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Call ProductSeeder
        $this->call(ProductSeeder::class);

        // Call BannerSeeder
        $this->call(BannerSeeder::class);
    }
}
