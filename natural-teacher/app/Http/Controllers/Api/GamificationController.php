<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Challenge;
use App\Models\Reward;
use App\Models\UserAchievement;
use App\Models\UserBadge;
use App\Models\UserReward;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GamificationController extends Controller
{
    protected $gamificationService;
    
    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }
    
    /**
     * Get user's gamification data.
     */
    public function getUserData()
    {
        $user = Auth::user();
        $profile = $user->profile;
        
        // Get achievements (both earned and available)
        $achievements = Achievement::where('is_active', true)
            ->get()
            ->map(function ($achievement) use ($user) {
                $userAchievement = UserAchievement::where('user_id', $user->id)
                    ->where('achievement_id', $achievement->id)
                    ->first();
                
                return [
                    'id' => $achievement->id,
                    'name' => $achievement->name,
                    'description' => $achievement->description,
                    'icon' => $achievement->icon,
                    'points_value' => $achievement->points_value,
                    'earned' => $userAchievement ? true : false,
                    'date_earned' => $userAchievement ? $userAchievement->date_earned : null,
                ];
            });
        
        // Get badges
        $badges = Badge::where('is_active', true)
            ->get()
            ->map(function ($badge) use ($user) {
                $userBadge = UserBadge::where('user_id', $user->id)
                    ->where('badge_id', $badge->id)
                    ->first();
                
                return [
                    'id' => $badge->id,
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'image_path' => $badge->image_path,
                    'required_points' => $badge->required_points,
                    'badge_type' => $badge->badge_type,
                    'earned' => $userBadge ? true : false,
                    'date_earned' => $userBadge ? $userBadge->date_earned : null,
                ];
            });
        
        // Get rewards
        $rewards = Reward::where('is_active', true)
            ->get()
            ->map(function ($reward) use ($user) {
                $userReward = UserReward::where('user_id', $user->id)
                    ->where('reward_id', $reward->id)
                    ->first();
                
                return [
                    'id' => $reward->id,
                    'name' => $reward->name,
                    'description' => $reward->description,
                    'image_path' => $reward->image_path,
                    'required_points' => $reward->required_points,
                    'is_redeemable' => $reward->is_redeemable,
                    'earned' => $userReward ? true : false,
                    'redeemed' => $userReward ? $userReward->is_redeemed : false,
                    'redemption_date' => $userReward ? $userReward->redemption_date : null,
                ];
            });
        
        // Get active challenges
        $challenges = Challenge::where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get()
            ->map(function ($challenge) use ($user) {
                $userChallenge = $user->challenges()
                    ->where('challenge_id', $challenge->id)
                    ->first();
                
                return [
                    'id' => $challenge->id,
                    'name' => $challenge->name,
                    'description' => $challenge->description,
                    'challenge_type' => $challenge->challenge_type,
                    'difficulty' => $challenge->difficulty,
                    'points_reward' => $challenge->points_reward,
                    'start_date' => $challenge->start_date,
                    'end_date' => $challenge->end_date,
                    'progress_percent' => $userChallenge ? $userChallenge->pivot->progress_percent : 0,
                    'completed' => $userChallenge && $userChallenge->pivot->completed_date ? true : false,
                    'completed_date' => $userChallenge ? $userChallenge->pivot->completed_date : null,
                    'points_earned' => $userChallenge ? $userChallenge->pivot->points_earned : 0,
                ];
            });
        
        return response()->json([
            'level' => $profile->current_level ?? 1,
            'total_points' => $profile->total_points ?? 1,
            'points_to_next_level' => $profile->nextLevelThreshold() - $profile->total_points ?? 1,
            'level_progress' => $profile->levelProgress(),
            'daily_streak' => $profile->daily_streak,
            'achievements' => $achievements,
            'badges' => $badges,
            'rewards' => $rewards,
            'challenges' => $challenges,
        ]);
    }
    
    /**
     * Check for new achievements a user has earned.
     */
    public function checkAchievements()
    {
        $user = Auth::user();
        $newAchievements = [];
        $updatedLevel = $user->profile->current_level ?? 1;
        $updatedPoints = $user->profile->total_points ?? 1;
        
        // Get all achievements that the user hasn't earned yet
        $unearnedAchievements = Achievement::where('is_active', true)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('achievement_id')
                    ->from('user_achievements')
                    ->where('user_id', $user->id);
            })
            ->get();
        
        // Check if user meets requirements for each achievement
        foreach ($unearnedAchievements as $achievement) {
            if ($achievement->checkRequirements($user)) {
                // Award the achievement
                $achievement->awardTo($user);
                
                // Add to new achievements array
                $newAchievements[] = [
                    'id' => $achievement->id,
                    'name' => $achievement->name,
                    'description' => $achievement->description,
                    'icon' => $achievement->icon,
                    'points_value' => $achievement->points_value,
                    'date_earned' => now()->format('Y-m-d H:i:s'),
                ];
                
                // Update the user's profile (points & level) for accurate reporting
                $user->refresh();
            }
        }
        
        // Check if any badges were unlocked by points
        $unlockedBadges = Badge::where('is_active', true)
            ->where('required_points', '<=', $user->profile->total_points ?? 1)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('badge_id')
                    ->from('user_badges')
                    ->where('user_id', $user->id);
            })
            ->get();
        
        // Award newly unlocked badges
        foreach ($unlockedBadges as $badge) {
            UserBadge::create([
                'user_id' => $user->id,
                'badge_id' => $badge->id,
                'date_earned' => now(),
            ]);
        }
        
        return response()->json([
            'new_achievements' => $newAchievements,
            'updated_level' => $user->profile->current_level ?? 1,
            'updated_points' => $user->profile->total_points ?? 1,
        ]);
    }
    
    /**
     * Redeem a reward.
     */
    public function redeemReward($rewardId)
    {
        $user = Auth::user();
        $reward = Reward::findOrFail($rewardId);
        
        // Check if user already earned this reward
        $userReward = UserReward::where('user_id', $user->id)
            ->where('reward_id', $rewardId)
            ->first();
        
        if (!$userReward) {
            // Check if user has enough points
            if ($user->profile->total_points < $reward->required_points) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough points to redeem this reward',
                ], 403);
            }
            
            // Create user reward record
            $userReward = UserReward::create([
                'user_id' => $user->id,
                'reward_id' => $rewardId,
                'is_redeemed' => true,
                'redemption_date' => now(),
            ]);
        } else if (!$userReward->is_redeemed) {
            // Update existing record
            $userReward->update([
                'is_redeemed' => true,
                'redemption_date' => now(),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Reward already redeemed',
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Reward redeemed successfully',
            'reward' => $reward,
        ]);
    }
    
    /**
     * Get leaderboard data.
     */
    public function getLeaderboard(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string|in:daily,weekly,monthly,all_time',
            'limit' => 'nullable|integer|min:5|max:100',
        ]);
        
        $type = $request->input('type', 'weekly');
        $limit = $request->input('limit', 10);
        
        $leaderboardData = $this->gamificationService->getLeaderboard($type, $limit);
        
        return response()->json($leaderboardData);
    }
}