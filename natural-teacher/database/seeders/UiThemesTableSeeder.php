<?php

namespace Database\Seeders;

use App\Models\UiTheme;
use Illuminate\Database\Seeder;

class UiThemesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Theme for Early Elementary (5-7)
        UiTheme::create([
            'name' => 'Playful Primary',
            'description' => 'A bright, colorful theme with rounded shapes for young learners.',
            'primary_color' => '#ff7043',
            'secondary_color' => '#4caf50',
            'accent_color' => '#ffeb3b',
            'font_family' => 'Nunito, sans-serif',
            'age_group_target' => '5-7',
            'is_default' => true,
        ]);
        
        // Theme for Elementary (8-10)
        UiTheme::create([
            'name' => 'Cool Explorer',
            'description' => 'A balanced theme with engaging visuals for elementary students.',
            'primary_color' => '#5c6bc0',
            'secondary_color' => '#26a69a',
            'accent_color' => '#ffa726',
            'font_family' => 'Nunito, sans-serif',
            'age_group_target' => '8-10',
            'is_default' => true,
        ]);
        
        // Theme for Middle School (11-15)
        UiTheme::create([
            'name' => 'Modern Scholar',
            'description' => 'A more sophisticated theme for middle school students.',
            'primary_color' => '#3949ab',
            'secondary_color' => '#00897b',
            'accent_color' => '#f57c00',
            'font_family' => 'Nunito, sans-serif',
            'age_group_target' => '11-15',
            'is_default' => true,
        ]);
        
        // Optional themes that can be unlocked
        UiTheme::create([
            'name' => 'Space Explorer',
            'description' => 'A cosmic theme with stars and space-inspired colors.',
            'primary_color' => '#303f9f',
            'secondary_color' => '#7b1fa2',
            'accent_color' => '#ffd600',
            'font_family' => 'Nunito, sans-serif',
            'age_group_target' => 'all',
            'is_default' => false,
        ]);
        
        UiTheme::create([
            'name' => 'Ocean Adventure',
            'description' => 'A cool blue underwater theme with ocean elements.',
            'primary_color' => '#0277bd',
            'secondary_color' => '#00838f',
            'accent_color' => '#ffc400',
            'font_family' => 'Nunito, sans-serif',
            'age_group_target' => 'all',
            'is_default' => false,
        ]);
    }
}
