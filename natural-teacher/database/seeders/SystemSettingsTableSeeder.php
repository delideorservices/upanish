<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Application settings
        SystemSetting::setValue('app_name', 'UpanishadAI', 'application', 'The name of the application', 'string', true);
        SystemSetting::setValue('app_description', 'AI-powered homework assistant for students aged 5-15', 'application', 'Application description', 'string', true);
        SystemSetting::setValue('app_logo', '/images/logo.png', 'application', 'Application logo path', 'string', true);
        SystemSetting::setValue('app_favicon', '/images/favicon.ico', 'application', 'Application favicon path', 'string', true);
        
        // AI Service settings
        SystemSetting::setValue('ai_service_url', 'http://127.0.0.1:5000', 'ai_service', 'URL for the Python AI service', 'string', true);
        SystemSetting::setValue('ai_timeout', 30, 'ai_service', 'Timeout in seconds for AI service requests', 'integer', true);
        SystemSetting::setValue('openai_model', 'gpt-4o', 'ai_service', 'Default OpenAI model to use', 'string', true);
        
        // Gamification settings
        SystemSetting::setValue('points_per_homework', 10, 'gamification', 'Base points awarded for completing homework', 'integer', true);
        SystemSetting::setValue('streak_bonus_multiplier', 1.5, 'gamification', 'Point multiplier for maintaining streaks', 'float', true);
        SystemSetting::setValue('level_up_announcement', true, 'gamification', 'Show level up announcements', 'boolean', true);
        SystemSetting::setValue('achievement_notification', true, 'gamification', 'Show achievement notifications', 'boolean', true);
        SystemSetting::setValue('leaderboard_enabled', true, 'gamification', 'Enable leaderboards', 'boolean', true);
        
        // Content adaptation settings
        SystemSetting::setValue('max_sentence_length_5_7', 10, 'content', 'Maximum sentence length for ages 5-7', 'integer', true);
        SystemSetting::setValue('max_sentence_length_8_10', 15, 'content', 'Maximum sentence length for ages 8-10', 'integer', true);
        SystemSetting::setValue('max_sentence_length_11_15', 20, 'content', 'Maximum sentence length for ages 11-15', 'integer', true);
        SystemSetting::setValue('use_illustrations_5_7', true, 'content', 'Use illustrations for ages 5-7', 'boolean', true);
        SystemSetting::setValue('use_illustrations_8_10', true, 'content', 'Use illustrations for ages 8-10', 'boolean', true);
        SystemSetting::setValue('use_illustrations_11_15', false, 'content', 'Use illustrations for ages 11-15', 'boolean', true);
        
        // Monitoring settings
        SystemSetting::setValue('allow_parent_monitoring', true, 'monitoring', 'Allow parents to monitor student activity', 'boolean', true);
        SystemSetting::setValue('allow_teacher_monitoring', true, 'monitoring', 'Allow teachers to monitor student activity', 'boolean', true);
        SystemSetting::setValue('monitoring_notification', true, 'monitoring', 'Notify students when being monitored', 'boolean', true);
        
        // Security settings
        SystemSetting::setValue('max_login_attempts', 5, 'security', 'Maximum login attempts before lockout', 'integer', true);
        SystemSetting::setValue('session_timeout', 60, 'security', 'Session timeout in minutes', 'integer', true);
        SystemSetting::setValue('require_email_verification', true, 'security', 'Require email verification for new accounts', 'boolean', true);
    }
}