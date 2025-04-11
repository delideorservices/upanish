<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaderboardEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'leaderboard_id',
        'user_id',
        'score',
        'rank',
        'period_start',
        'period_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * Get the leaderboard for this entry.
     */
    public function leaderboard()
    {
        return $this->belongsTo(Leaderboard::class);
    }

    /**
     * Get the user for this entry.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
