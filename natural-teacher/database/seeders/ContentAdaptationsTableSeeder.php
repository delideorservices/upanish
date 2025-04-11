<?php

namespace Database\Seeders;

use App\Models\AgeGroup;
use App\Models\ContentAdaptation;
use Illuminate\Database\Seeder;

class ContentAdaptationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get age group IDs
        $earlyElementaryId = AgeGroup::where('name', 'Early Elementary')->value('id');
        $elementaryId = AgeGroup::where('name', 'Elementary')->value('id');
        $middleSchoolId = AgeGroup::where('name', 'Middle School')->value('id');
        
        if ($earlyElementaryId) {
            // Early Elementary (5-7) adaptations
            ContentAdaptation::create([
                'age_group_id' => $earlyElementaryId,
                'content_type' => 'text',
                'vocabulary_limit' => 100,
                'sentence_length' => 10,
                'use_illustrations' => true,
            ]);
            
            ContentAdaptation::create([
                'age_group_id' => $earlyElementaryId,
                'content_type' => 'math',
                'vocabulary_limit' => 50,
                'sentence_length' => 8,
                'use_illustrations' => true,
            ]);
        }
        
        if ($elementaryId) {
            // Elementary (8-10) adaptations
            ContentAdaptation::create([
                'age_group_id' => $elementaryId,
                'content_type' => 'text',
                'vocabulary_limit' => 200,
                'sentence_length' => 15,
                'use_illustrations' => true,
            ]);
            
            ContentAdaptation::create([
                'age_group_id' => $elementaryId,
                'content_type' => 'math',
                'vocabulary_limit' => 150,
                'sentence_length' => 12,
                'use_illustrations' => true,
            ]);
        }
        
        if ($middleSchoolId) {
            // Middle School (11-15) adaptations
            ContentAdaptation::create([
                'age_group_id' => $middleSchoolId,
                'content_type' => 'text',
                'vocabulary_limit' => 500,
                'sentence_length' => 20,
                'use_illustrations' => false,
            ]);
            
            ContentAdaptation::create([
                'age_group_id' => $middleSchoolId,
                'content_type' => 'math',
                'vocabulary_limit' => 300,
                'sentence_length' => 18,
                'use_illustrations' => true,
            ]);
        }
    }
}
