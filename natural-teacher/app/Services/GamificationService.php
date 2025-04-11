<?php

namespace App\Services;

use App\Models\User;
use App\Models\Topic;
use App\Models\Achievement;
use App\Models\SystemSetting;
use App\Models\Session;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Award points for completing homework.
     *
     * @param User $user
     * @param Topic $topic
     * @return int Points awarded
     */
    public function awardHomeworkPoints(User $user, Topic $topic): int
    {
        $profile = $user->profile;
        $basePoints = $topic->points_available;
        $streakMultiplier = 1.0;
        
        // Apply streak bonus if applicable
        if ($profile->daily_streak ?? 1 >= 3) {
            $streakMultiplier = SystemSetting::getValue(
                'streak_bonus_multiplier', 
                1.5
            );
        }
        
        $pointsToAward = ceil($basePoints * $streakMultiplier);
        
        // Update user profile
        // $profile->addPoints($pointsToAward);
        
        return $pointsToAward;
    }
    
    /**
     * Award bonus points for providing feedback.
     *
     * @param User $user
     * @return int Points awarded
     */
    public function awardFeedbackPoints(User $user): int
    {
        $bonusPoints = SystemSetting::getValue('feedback_bonus_points', 2);
        
        // Update user profile
        $user->profile->addPoints($bonusPoints);
        
        return $bonusPoints;
    }
    
    /**
     * Get leaderboard data.
     *
     * @param string $type
     * @param int $limit
     * @return array
     */
    public function getLeaderboard(string $type = 'weekly', int $limit = 10): array
    {
        $query = User::with('profile')
            ->whereHas('profile')
            ->where('role', 'student');
        
        // Apply time filters based on type
        if ($type === 'daily') {
            $query->whereHas('sessions', function ($q) {
                $q->whereDate('created_at', today());
            });
        } elseif ($type === 'weekly') {
            $query->whereHas('sessions', function ($q) {
                $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            });
        } elseif ($type === 'monthly') {
            $query->whereHas('sessions', function ($q) {
                $q->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
            });
        }
        
        // Get users and sort by points
        $users = $query->get()
            ->map(function ($user) use ($type) {
                $pointsEarned = $this->calculatePointsForPeriod($user, $type);
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'avatar' => $user->profile->avatar,
                    'level' => $user->profile->current_level,
                    'points' => $pointsEarned,
                ];
            })
            ->sortByDesc('points')
            ->take($limit)
            ->values()
            ->toArray();
        
        // Add ranks
        $rank = 1;
        foreach ($users as &$user) {
            $user['rank'] = $rank++;
        }
        
        // Get current user's rank
        $currentUser = auth()->user();
        $currentUserRank = null;
        
        if ($currentUser) {
            $allUsers = User::with('profile')
                ->whereHas('profile')
                ->where('role', 'student')
                ->get()
                ->map(function ($user) use ($type) {
                    return [
                        'id' => $user->id,
                        'points' => $this->calculatePointsForPeriod($user, $type),
                    ];
                })
                ->sortByDesc('points')
                ->values();
            
            $currentUserIndex = $allUsers->search(function ($item) use ($currentUser) {
                return $item['id'] === $currentUser->id;
            });
            
            if ($currentUserIndex !== false) {
                $currentUserRank = $currentUserIndex + 1;
            }
        }
        
        return [
            'leaderboard' => $users,
            'current_user_rank' => $currentUserRank,
            'type' => $type,
        ];
    }
    
    /**
     * Calculate points earned for a specific time period.
     *
     * @param User $user
     * @param string $type
     * @return int
     */
    private function calculatePointsForPeriod(User $user, string $type): int
    {
        if ($type === 'all_time') {
            return $user->profile->total_points;
        }
        
        $query = Session::where('user_id', $user->id)
            ->where('status', 'completed');
        
        if ($type === 'daily') {
            $query->whereDate('created_at', today());
        } elseif ($type === 'weekly') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($type === 'monthly') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        }
        
        return $query->sum('points_earned');
    }
    
    /**
     * Check and update daily streaks for all users.
     * This would typically be run by a scheduled task.
     */
    public function updateDailyStreaks(): void
    {
        // Get users who have logged in today
        $todayUsers = User::whereHas('sessions', function ($query) {
            $query->whereDate('created_at', today());
        })->get();
        
        foreach ($todayUsers as $user) {
            $user->profile->updateStreak();
        }
        
        // Reset streaks for users who didn't log in yesterday
        $yesterday = now()->subDay();
        User::whereDoesntHave('sessions', function ($query) use ($yesterday) {
            $query->whereDate('created_at', $yesterday);
        })
        ->whereHas('profile', function ($query) {
            $query->where('daily_streak', '>', 0);
        })
        ->get()
        ->each(function ($user) {
            $user->profile->update(['daily_streak' => 0]);
        });
    }
}