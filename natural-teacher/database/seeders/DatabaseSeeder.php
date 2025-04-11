<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemSettingsTableSeeder::class,
            UiThemesTableSeeder::class,
            AgeGroupsTableSeeder::class,
            SubjectsTableSeeder::class,
            TopicsTableSeeder::class,
            AchievementsTableSeeder::class,
            BadgesTableSeeder::class,
            RewardsTableSeeder::class,
            ContentAdaptationsTableSeeder::class,
            
            // Only uncomment for demo data
            // UsersTableSeeder::class,
        ]);
    }
}