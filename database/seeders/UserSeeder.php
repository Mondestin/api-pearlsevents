<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@pearlevents.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        // Create default client user
        User::create([
            'name' => 'Client User',
            'email' => 'client@pearlevents.com',
            'password' => Hash::make('password'),
            'role' => 'client',
            'email_verified_at' => now(),
        ]);

        // Create additional admin users
        User::factory()->admin()->count(3)->create();

        // Create additional client users
        User::factory()->client()->count(20)->create();
    }
} 