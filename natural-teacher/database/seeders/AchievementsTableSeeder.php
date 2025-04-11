<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Login achievements
        Achievement::create([
            'name' => 'First Day',
            'description' => 'Completed your first day of learning.',
            'icon' => 'fa-calendar-check',
            'points_value' => 10,
            'achievement_type' => 'login',
            'requirements' => json_encode(['login_count' => 1]),
            'is_active' => true,
        ]);
        
        Achievement::create([
            'name' => 'One Week Streak',
            'description' => 'Logged in for 7 consecutive days.',
            'icon' => 'fa-calendar-week',
            'points_value' => 25,
            'achievement_type' => 'login',
            'requirements' => json_encode(['streak_days' => 7]),
            'is_active' => true,
        ]);
        
        Achievement::create([
            'name' => 'Dedicated Learner',
            'description' => 'Maintained a 30-day login streak.',
            'icon' => 'fa-trophy',
            'points_value' => 100,
            'achievement_type' => 'login',
            'requirements' => json_encode(['streak_days' => 30]),
            'is_active' => true,
        ]);
        
        // Session achievements
        Achievement::create([
            'name' => 'First Homework',
            'description' => 'Completed your first homework session.',
            'icon' => 'fa-book',
            'points_value' => 15,
            'achievement_type' => 'session',
            'requirements' => json_encode(['session_count' => 1]),
            'is_active' => true,
        ]);
        
        Achievement::create([
            'name' => 'Homework Hero',
            'description' => 'Completed 10 homework sessions.',
            'icon' => 'fa-award',
            'points_value' => 50,
            'achievement_type' => 'session',
            'requirements' => json_encode(['session_count' => 10]),
            'is_active' => true,
        ]);
        
        Achievement::create([
            'name' => 'Master Student',
            'description' => 'Completed 50 homework sessions.',
            'icon' => 'fa-graduation-cap',
            'points_value' => 200,
            'achievement_type' => 'session',
            'requirements' => json_encode(['session_count' => 50]),
            'is_active' => true,
        ]);
        
        // Subject achievements - Math
        Achievement::create([
            'name' => 'Math Explorer',
            'description' => 'Completed 5 math homework sessions.',
            'icon' => 'fa-calculator',
            'points_value' => 30,
            'achievement_type' => 'subject',
            'requirements' => json_encode([
                'subject_id' => 1, // Math subject ID
                'sessions_completed' => 5
            ]),
            'is_active' => true,
        ]);
        
        Achievement::create([
            'name' => 'Math Wizard',
            'description' => 'Completed 25 math homework sessions.',
            'icon' => 'fa-square-root-alt',
            'points_value' => 100,
            'achievement_type' => 'subject',
            'requirements' => json_encode([
                'subject_id' => 1, // Math subject ID
                'sessions_completed' => 25
            ]),
            'is_active' => true,
        ]);
        
        // Subject achievements - English
        Achievement::create([
            'name' => 'Word Seeker',
            'description' => 'Completed 5 English homework sessions.',
            'icon' => 'fa-book-open',
            'points_value' => 30,
            'achievement_type' => 'subject',
            'requirements' => json_encode([
                'subject_id' => 2, // English subject ID
                'sessions_completed' => 5
            ]),
            'is_active' => true,
        ]);
        
        Achievement::create([
            'name' => 'Language Master',
            'description' => 'Completed 25 English homework sessions.',
            'icon' => 'fa-pen-fancy',
            'points_value' => 100,
            'achievement_type' => 'subject',
            'requirements' => json_encode([
                'subject_id' => 2, // English subject ID
                'sessions_completed' => 25
            ]),
            'is_active' => true,
        ]);
    }
}