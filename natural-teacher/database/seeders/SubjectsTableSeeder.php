<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subject::create([
            'name' => 'Mathematics',
            'description' => 'Learn mathematics concepts, problem-solving, and calculations for all grade levels.',
            'icon' => 'fa-calculator',
            'display_order' => 1,
            'is_active' => true,
        ]);
        
        Subject::create([
            'name' => 'English',
            'description' => 'Develop reading, writing, grammar, and language arts skills.',
            'icon' => 'fa-book',
            'display_order' => 2,
            'is_active' => true,
        ]);
    }
}