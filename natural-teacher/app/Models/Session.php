<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'subject_id',
        'topic_id',
        'start_time',
        'end_time',
        'duration',
        'status',
        'points_earned',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject for this session.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the topic for this session.
     */
    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    /**
     * Get the questions for this session.
     */
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    /**
     * Calculate the session duration if not set.
     */
    public function getDurationAttribute($value)
    {
        if ($value) {
            return $value;
        }

        if ($this->start_time && $this->end_time) {
            return $this->end_time->diffInSeconds($this->start_time);
        }

        return null;
    }
}
