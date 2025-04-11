<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'avatar',
        'preferred_learning_style',
        'difficulty_level',
        'current_level',
        'total_points',
        'daily_streak',
        'last_login_date',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the next level threshold.
     */
    public function nextLevelThreshold()
    {
        // Formula: Base points (100) + (Current level * Points multiplier (50))
        return 100 + ($this->current_level * 50);
    }

    /**
     * Calculate progress to next level as percentage.
     */
    public function levelProgress()
    {
        $nextThreshold = $this->nextLevelThreshold();
        $currentThreshold = 100 + (($this->current_level - 1) * 50);
        $pointsInCurrentLevel = $this->total_points - $currentThreshold;
        $pointsNeededForNextLevel = $nextThreshold - $currentThreshold;
        
        return min(100, round(($pointsInCurrentLevel / $pointsNeededForNextLevel) * 100));
    }

    /**
     * Add points and handle level ups.
     */
    public function addPoints($points)
    {
        $this->total_points += $points;
        
        // Check for level up
        while ($this->total_points >= $this->nextLevelThreshold()) {
            $this->current_level += 1;
        }
        
        $this->save();
        
        return $this->current_level;
    }

    /**
     * Update streak based on login.
     */
    public function updateStreak()
    {
        $today = now()->format('Y-m-d');
        $lastLogin = $this->last_login_date;
        
        if (!$lastLogin) {
            // First login
            $this->daily_streak = 1;
        } elseif ($lastLogin->addDay()->format('Y-m-d') === $today) {
            // Consecutive day
            $this->daily_streak += 1;
        } elseif ($lastLogin->format('Y-m-d') !== $today) {
            // Missed a day, reset streak
            $this->daily_streak = 1;
        }
        
        $this->last_login_date = now();
        $this->save();
        
        return $this->daily_streak;
    }
}