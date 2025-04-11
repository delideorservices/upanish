<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
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
        'is_redeemable',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_redeemable' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users who have earned this reward.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_rewards')
            ->withPivot('is_redeemed', 'redemption_date')
            ->withTimestamps();
    }

    /**
     * Check if reward can be redeemed by a user.
     */
    public function canBeRedeemedBy(User $user)
    {
        // Check if the reward is active and redeemable
        if (!$this->is_active || !$this->is_redeemable) {
            return false;
        }
        
        // Check if user has enough points
        if ($user->profile->total_points < $this->required_points) {
            return false;
        }
        
        // Check if the user has already redeemed this reward
        $userReward = UserReward::where('user_id', $user->id)
            ->where('reward_id', $this->id)
            ->where('is_redeemed', true)
            ->first();
            
        return !$userReward;
    }
}
