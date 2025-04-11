<?php

namespace Database\Seeders;

use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get subject IDs
        $mathSubject = Subject::where('name', 'Mathematics')->first();
        $englishSubject = Subject::where('name', 'English')->first();
        
        if ($mathSubject) {
            // Mathematics topics
            Topic::create([
                'subject_id' => $mathSubject->id,
                'name' => 'Counting and Numbers',
                'description' => 'Learn to count and recognize numbers.',
                'age_group_min' => 5,
                'age_group_max' => 7,
                'difficulty_level' => 1,
                'points_available' => 10,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $mathSubject->id,
                'name' => 'Addition and Subtraction',
                'description' => 'Basic addition and subtraction operations.',
                'age_group_min' => 5,
                'age_group_max' => 8,
                'difficulty_level' => 1,
                'points_available' => 15,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $mathSubject->id,
                'name' => 'Multiplication and Division',
                'description' => 'Learn multiplication tables and division concepts.',
                'age_group_min' => 7,
                'age_group_max' => 10,
                'difficulty_level' => 2,
                'points_available' => 20,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $mathSubject->id,
                'name' => 'Fractions and Decimals',
                'description' => 'Understanding and working with fractions and decimal numbers.',
                'age_group_min' => 8,
                'age_group_max' => 12,
                'difficulty_level' => 3,
                'points_available' => 25,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $mathSubject->id,
                'name' => 'Geometry',
                'description' => 'Shapes, angles, and spatial reasoning.',
                'age_group_min' => 9,
                'age_group_max' => 15,
                'difficulty_level' => 3,
                'points_available' => 25,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $mathSubject->id,
                'name' => 'Algebra',
                'description' => 'Variables, equations, and algebraic thinking.',
                'age_group_min' => 11,
                'age_group_max' => 15,
                'difficulty_level' => 4,
                'points_available' => 30,
                'is_active' => true,
            ]);
        }
        
        if ($englishSubject) {
            // English topics
            Topic::create([
                'subject_id' => $englishSubject->id,
                'name' => 'Alphabet and Phonics',
                'description' => 'Learn letters, sounds, and basic reading skills.',
                'age_group_min' => 5,
                'age_group_max' => 7,
                'difficulty_level' => 1,
                'points_available' => 10,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $englishSubject->id,
                'name' => 'Grammar Basics',
                'description' => 'Learn about nouns, verbs, and sentence structure.',
                'age_group_min' => 6,
                'age_group_max' => 9,
                'difficulty_level' => 1,
                'points_available' => 15,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $englishSubject->id,
                'name' => 'Reading Comprehension',
                'description' => 'Understand and analyze reading passages.',
                'age_group_min' => 7,
                'age_group_max' => 12,
                'difficulty_level' => 2,
                'points_available' => 20,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $englishSubject->id,
                'name' => 'Vocabulary Building',
                'description' => 'Expand vocabulary and word usage.',
                'age_group_min' => 8,
                'age_group_max' => 15,
                'difficulty_level' => 2,
                'points_available' => 20,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $englishSubject->id,
                'name' => 'Writing Skills',
                'description' => 'Develop effective writing skills for various purposes.',
                'age_group_min' => 9,
                'age_group_max' => 15,
                'difficulty_level' => 3,
                'points_available' => 25,
                'is_active' => true,
            ]);
            
            Topic::create([
                'subject_id' => $englishSubject->id,
                'name' => 'Literature Analysis',
                'description' => 'Analyze and interpret literature, poems, and stories.',
                'age_group_min' => 11,
                'age_group_max' => 15,
                'difficulty_level' => 4,
                'points_available' => 30,
                'is_active' => true,
            ]);
        }
    }
}