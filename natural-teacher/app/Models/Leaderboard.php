<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leaderboard extends Model
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
        'leaderboard_type',
        'reset_frequency',
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
     * Get the entries for this leaderboard.
     */
    public function entries()
    {
        return $this->hasMany(LeaderboardEntry::class);
    }

    /**
     * Get current period entries.
     */
    public function currentPeriodEntries()
    {
        $now = now();
        
        // Determine period based on reset_frequency
        $periodStart = match($this->reset_frequency) {
            'daily' => $now->startOfDay(),
            'weekly' => $now->startOfWeek(),
            'monthly' => $now->startOfMonth(),
            default => $now->startOfWeek(),
        };
        
        $periodEnd = match($this->reset_frequency) {
            'daily' => $now->endOfDay(),
            'weekly' => $now->endOfWeek(),
            'monthly' => $now->endOfMonth(),
            default => $now->endOfWeek(),
        };
        
        return $this->entries()
            ->where('period_start', '=', $periodStart)
            ->where('period_end', '=', $periodEnd)
            ->orderBy('rank', 'asc');
    }
}
