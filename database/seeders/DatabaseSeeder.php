<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $user = \App\Models\User::factory()->create([
            'name' => 'Test Resident',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'branch_id' => null,
            'room_id' => null,
            'seat_number' => null,
            'booking_start_date' => now()->subDays(2)->format('Y-m-d'),
            'booking_end_date'   => now()->addDays(8)->format('Y-m-d'),
        ]);
        
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@younic.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create Demo Branches
        $branch1 = \App\Models\Branch::create([
            'name' => 'Younic Home - Uttara',
            'address' => 'Sector 4, Uttara, Dhaka'
        ]);

        $branch2 = \App\Models\Branch::create([
            'name' => 'Younic Home - Mirpur',
            'address' => 'Mirpur 10, Dhaka'
        ]);

        // Create Demo Rooms for Branch 1
        \App\Models\Room::create(['branch_id' => $branch1->id, 'room_number' => '101', 'room_type' => '2-Seat', 'capacity' => 2, 'daily_rent' => 250]);
        \App\Models\Room::create(['branch_id' => $branch1->id, 'room_number' => '102', 'room_type' => '3-Seat', 'capacity' => 3, 'daily_rent' => 200]);
        \App\Models\Room::create(['branch_id' => $branch1->id, 'room_number' => '103', 'room_type' => '4-Seat', 'capacity' => 4, 'daily_rent' => 150]);

        // Create Demo Rooms for Branch 2
        \App\Models\Room::create(['branch_id' => $branch2->id, 'room_number' => '201', 'room_type' => 'Single', 'capacity' => 1, 'daily_rent' => 400]);
        \App\Models\Room::create(['branch_id' => $branch2->id, 'room_number' => '202', 'room_type' => '2-Seat', 'capacity' => 2, 'daily_rent' => 250]);
    }
}
