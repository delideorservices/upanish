<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Login badges
        Badge::create([
            'name' => 'Dedicated Student',
            'description' => 'Earned by logging in consistently and maintaining your study habits.',
            'image_path' => 'images/badges/dedicated_student.png',
            'required_points' => 100,
            'badge_type' => 'login',
            'is_active' => true,
        ]);
        
        // Subject mastery badges - Math
        Badge::create([
            'name' => 'Math Apprentice',
            'description' => 'Taking your first steps into mathematical mastery.',
            'image_path' => 'images/badges/math_apprentice.png',
            'required_points' => 200,
            'badge_type' => 'subject_math',
            'is_active' => true,
        ]);
        
        Badge::create([
            'name' => 'Math Master',
            'description' => 'A true master of mathematical concepts.',
            'image_path' => 'images/badges/math_master.png',
            'required_points' => 500,
            'badge_type' => 'subject_math',
            'is_active' => true,
        ]);
        
        // Subject mastery badges - English
        Badge::create([
            'name' => 'Word Weaver',
            'description' => 'Crafting language with skill and creativity.',
            'image_path' => 'images/badges/word_weaver.png',
            'required_points' => 200,
            'badge_type' => 'subject_english',
            'is_active' => true,
        ]);
        
        Badge::create([
            'name' => 'Language Luminary',
            'description' => 'A beacon of excellence in language arts.',
            'image_path' => 'images/badges/language_luminary.png',
            'required_points' => 500,
            'badge_type' => 'subject_english',
            'is_active' => true,
        ]);
        
        // Progress badges
        Badge::create([
            'name' => 'Quick Learner',
            'description' => 'Demonstrates exceptional progress in a short time.',
            'image_path' => 'images/badges/quick_learner.png',
            'required_points' => 300,
            'badge_type' => 'progress',
            'is_active' => true,
        ]);
        
        Badge::create([
            'name' => 'Knowledge Seeker',
            'description' => 'Always curious, always learning.',
            'image_path' => 'images/badges/knowledge_seeker.png',
            'required_points' => 400,
            'badge_type' => 'progress',
            'is_active' => true,
        ]);
        
        // Level badges
        Badge::create([
            'name' => 'Rising Star',
            'description' => 'Reached level 5 in your learning journey.',
            'image_path' => 'images/badges/rising_star.png',
            'required_points' => 250,
            'badge_type' => 'level',
            'is_active' => true,
        ]);
        
        Badge::create([
            'name' => 'Learning Legend',
            'description' => 'Reached level 10 in your learning journey.',
            'image_path' => 'images/badges/learning_legend.png',
            'required_points' => 1000,
            'badge_type' => 'level',
            'is_active' => true,
        ]);
    }
}
