<?php

namespace Database\Seeders;

use App\Models\Reward;
use Illuminate\Database\Seeder;

class RewardsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Avatar customizations
        Reward::create([
            'name' => 'Superhero Avatar Set',
            'description' => 'Unlock superhero-themed avatars for your profile.',
            'image_path' => 'images/rewards/superhero_avatars.png',
            'required_points' => 150,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
        
        Reward::create([
            'name' => 'Animal Avatar Set',
            'description' => 'Unlock animal-themed avatars for your profile.',
            'image_path' => 'images/rewards/animal_avatars.png',
            'required_points' => 150,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
        
        // Theme customizations
        Reward::create([
            'name' => 'Space Theme',
            'description' => 'A cosmic theme for your learning dashboard.',
            'image_path' => 'images/rewards/space_theme.png',
            'required_points' => 300,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
        
        Reward::create([
            'name' => 'Ocean Theme',
            'description' => 'An underwater theme for your learning dashboard.',
            'image_path' => 'images/rewards/ocean_theme.png',
            'required_points' => 300,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
        
        // Digital certificates
        Reward::create([
            'name' => 'Math Explorer Certificate',
            'description' => 'A digital certificate recognizing your mathematics achievements.',
            'image_path' => 'images/rewards/math_certificate.png',
            'required_points' => 500,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
        
        Reward::create([
            'name' => 'Language Arts Certificate',
            'description' => 'A digital certificate recognizing your language arts achievements.',
            'image_path' => 'images/rewards/language_certificate.png',
            'required_points' => 500,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
        
        // Special features
        Reward::create([
            'name' => 'Challenge Creator',
            'description' => 'Create custom challenges for yourself or friends.',
            'image_path' => 'images/rewards/challenge_creator.png',
            'required_points' => 800,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
        
        Reward::create([
            'name' => 'Advanced Problem Set',
            'description' => 'Unlock a special set of advanced problems for extra practice.',
            'image_path' => 'images/rewards/advanced_problems.png',
            'required_points' => 1000,
            'is_redeemable' => true,
            'is_active' => true,
        ]);
    }
}
