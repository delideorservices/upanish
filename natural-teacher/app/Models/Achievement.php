<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'icon',
        'points_value',
        'achievement_type',
        'requirements',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requirements' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users who have earned this achievement.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_achievements')
            ->withPivot('date_earned')
            ->withTimestamps();
    }

    /**
     * Check if achievement is completed for a user.
     */
    public function isCompletedBy(User $user)
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Award this achievement to a user.
     */
    public function awardTo(User $user)
    {
        // Only award if not already awarded
        if (!$this->isCompletedBy($user)) {
            $this->users()->attach($user->id, [
                'date_earned' => now(),
            ]);
            
            // Add points to user profile
            // $user->profile->addPoints($this->points_value);
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if a user meets requirements for this achievement.
     */
    public function checkRequirements(User $user)
    {
        $metRequirements = true;
        $requirements = $this->requirements;
        
        switch ($this->achievement_type) {
            case 'login':
                // Check login streak requirements
                if (isset($requirements['streak_days'])) {
                    $metRequirements = $user->profile->daily_streak >= $requirements['streak_days'];
                }
                break;
                
            case 'session':
                // Check session count requirements
                if (isset($requirements['session_count'])) {
                    $sessionCount = $user->sessions()->count();
                    $metRequirements = $sessionCount >= $requirements['session_count'];
                }
                break;
                
            case 'subject':
                // Check subject mastery requirements
                if (isset($requirements['subject_id']) && isset($requirements['sessions_completed'])) {
                    $subjectSessionsCount = $user->sessions()
                        ->where('subject_id', $requirements['subject_id'])
                        ->count();
                    $metRequirements = $subjectSessionsCount >= $requirements['sessions_completed'];
                }
                break;
                
            // Add more achievement types as needed
                
            default:
                $metRequirements = false;
                break;
        }
        
        return $metRequirements;
    }
}
