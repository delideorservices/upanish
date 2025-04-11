<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
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
        'display_order',
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
     * Get the topics associated with the subject.
     */
    public function topics()
    {
        return $this->hasMany(Topic::class);
    }

    /**
     * Get the active topics associated with the subject.
     */
    public function activeTopics()
    {
        return $this->topics()->where('is_active', true);
    }

    /**
     * Get topics appropriate for a specific age.
     */
    public function topicsForAge($age)
    {
        return $this->topics()
            ->where('is_active', true)
            ->where('age_group_min', '<=', $age)
            ->where('age_group_max', '>=', $age);
    }

    /**
     * Get the sessions associated with the subject.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }
}