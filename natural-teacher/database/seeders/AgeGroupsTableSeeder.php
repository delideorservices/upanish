<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use Illuminate\Database\Seeder;

class AgeGroupsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AgeGroup::create([
            'name' => 'Early Elementary',
            'min_age' => 5,
            'max_age' => 7,
            'complexity_level' => 1,
            'vocabulary_level' => 'basic',
            'default_theme_id' => 1, // Will be set in UI Themes seeder
        ]);
        
        AgeGroup::create([
            'name' => 'Elementary',
            'min_age' => 8,
            'max_age' => 10,
            'complexity_level' => 2,
            'vocabulary_level' => 'intermediate',
            'default_theme_id' => 2, // Will be set in UI Themes seeder
        ]);
        
        AgeGroup::create([
            'name' => 'Middle School',
            'min_age' => 11,
            'max_age' => 15,
            'complexity_level' => 3,
            'vocabulary_level' => 'advanced',
            'default_theme_id' => 3, // Will be set in UI Themes seeder
        ]);
    }
}