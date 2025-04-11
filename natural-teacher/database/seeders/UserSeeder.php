<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
  // In your Laravel database seeder
public function run()
{
    // Create a student user
    \App\Models\User::create([
        'name' => 'Test Student',
        'email' => 'student@example.com',
        'password' => Hash::make('password123'),
        'role' => 'student',
        'age' => 10,
        'age_group_id' => 2, // Elementary (8-10)
    ])->profile()->create([
        'preferred_learning_style' => 'visual',
        'current_level' => 1,
        'total_points' => 0,
        'daily_streak' => 1,
        'last_login_date' => now(),
    ]);

    // Create a parent user
    \App\Models\User::create([
        'name' => 'Test Parent',
        'email' => 'parent@example.com',
        'password' => Hash::make('password123'),
        'role' => 'parent',
    ])->profile()->create([
        'current_level' => 1,
        'total_points' => 0,
    ]);

    // Create a teacher user
    \App\Models\User::create([
        'name' => 'Test Teacher',
        'email' => 'teacher@example.com',
        'password' => Hash::make('password123'),
        'role' => 'teacher',
    ])->profile()->create([
        'current_level' => 1,
        'total_points' => 0,
    ]);
}

}
