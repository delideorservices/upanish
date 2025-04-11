<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
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
        'challenge_type',
        'difficulty',
        'points_reward',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users participating in this challenge.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_challenges')
            ->withPivot('progress_percent', 'completed_date', 'points_earned')
            ->withTimestamps();
    }

      /**
     * Check if challenge is active.
     */
    public function isActive()
    {
        $now = now();
        return $this->is_active && 
               $now->greaterThanOrEqualTo($this->start_date) && 
               $now->lessThanOrEqualTo($this->end_date);
    }

    /**
     * Check if a user has completed this challenge.
     */
    public function isCompletedBy(User $user)
    {
        $userChallenge = $this->users()
            ->where('user_id', $user->id)
            ->first();
            
        return $userChallenge && $userChallenge->pivot->completed_date;
    }

    /**
     * Get progress for a specific user.
     */
    public function getProgressFor(User $user)
    {
        $userChallenge = $this->users()
            ->where('user_id', $user->id)
            ->first();
            
        return $userChallenge ? $userChallenge->pivot->progress_percent : 0;
    }

    /**
     * Update progress for a user.
     */
    public function updateProgressFor(User $user, int $progressPercent)
    {
        $progressPercent = min(100, max(0, $progressPercent));
        
        $userChallenge = $this->users()
            ->where('user_id', $user->id)
            ->first();
            
        if (!$userChallenge) {
            // Add user to challenge
            $this->users()->attach($user->id, [
                'progress_percent' => $progressPercent,
                'completed_date' => $progressPercent >= 100 ? now() : null,
                'points_earned' => $progressPercent >= 100 ? $this->points_reward : 0,
            ]);
        } else {
            // Update existing record
            $this->users()->updateExistingPivot($user->id, [
                'progress_percent' => $progressPercent,
                'completed_date' => $progressPercent >= 100 ? now() : $userChallenge->pivot->completed_date,
                'points_earned' => $progressPercent >= 100 ? $this->points_reward : $userChallenge->pivot->points_earned,
            ]);
        }
        
        // If challenge was just completed, award points
        if ($progressPercent >= 100 && (!$userChallenge || !$userChallenge->pivot->completed_date)) {
            $user->profile->addPoints($this->points_reward);
        }
        
        return $progressPercent;
    }
}
