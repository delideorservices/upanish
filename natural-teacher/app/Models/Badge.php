<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
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
        'image_path',
        'required_points',
        'badge_type',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the users who have earned this badge.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('date_earned')
            ->withTimestamps();
    }

    /**
     * Check if badge is earned by a user.
     */
    public function isEarnedBy(User $user)
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Award this badge to a user.
     */
    public function awardTo(User $user)
    {
        // Only award if not already awarded
        if (!$this->isEarnedBy($user)) {
            $this->users()->attach($user->id, [
                'date_earned' => now(),
            ]);
            
            return true;
        }
        
        return false;
    }
}
