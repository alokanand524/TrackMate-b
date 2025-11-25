<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Setting;
use App\Models\OfficeZone;
use Illuminate\Support\Facades\Hash;

class DefaultDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@trackmate.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '+1234567890',
                'is_active' => true,
            ]
        );

        // Create default settings
        $defaultSettings = [
            [
                'key' => 'work_start_time',
                'value' => '09:00',
                'type' => 'string',
                'description' => 'Office work start time'
            ],
            [
                'key' => 'work_end_time',
                'value' => '18:00',
                'type' => 'string',
                'description' => 'Office work end time'
            ],
            [
                'key' => 'break_duration_minutes',
                'value' => '60',
                'type' => 'integer',
                'description' => 'Default break duration in minutes'
            ],
            [
                'key' => 'late_threshold_minutes',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Late threshold in minutes after work start time'
            ],
            [
                'key' => 'company_name',
                'value' => 'TrackMate Company',
                'type' => 'string',
                'description' => 'Company name'
            ],
            [
                'key' => 'timezone',
                'value' => 'UTC',
                'type' => 'string',
                'description' => 'Company timezone'
            ]
        ];

        foreach ($defaultSettings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        // Create a sample office zone (you can update coordinates later)
        OfficeZone::firstOrCreate(
            ['name' => 'Main Office'],
            [
                'latitude' => 40.7128,  // New York coordinates as example
                'longitude' => -74.0060,
                'radius_meters' => 100,
                'address' => '123 Main Street, New York, NY 10001',
                'is_active' => true,
            ]
        );

        $this->command->info('Default data seeded successfully!');
        $this->command->info('Admin credentials:');
        $this->command->info('Email: admin@trackmate.com');
        $this->command->info('Password: password123');
    }
}