<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_id',
        'name',
        'description',
        'age_group_min',
        'age_group_max',
        'difficulty_level',
        'points_available',
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
     * Get the subject that owns the topic.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the sessions for this topic.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Check if this topic is appropriate for a user's age.
     */
    public function isAppropriateFor(User $user)
    {
        return $user->age >= $this->age_group_min && 
               $user->age <= $this->age_group_max;
    }

    /**
     * Get topics by difficulty level.
     */
    public static function getByDifficulty($difficultyLevel)
    {
        return self::where('difficulty_level', $difficultyLevel)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Get topics appropriate for a specific age.
     */
    public static function getForAge($age)
    {
        return self::where('age_group_min', '<=', $age)
            ->where('age_group_max', '>=', $age)
            ->where('is_active', true)
            ->get();
    }
}
