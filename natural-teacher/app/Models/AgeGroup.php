<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgeGroup extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'min_age',
        'max_age',
        'complexity_level',
        'vocabulary_level',
        'default_theme_id',
    ];

    /**
     * Get the default theme for this age group.
     */
    public function defaultTheme()
    {
        return $this->belongsTo(UiTheme::class, 'default_theme_id');
    }

    /**
     * Get the content adaptations for this age group.
     */
    public function contentAdaptations()
    {
        return $this->hasMany(ContentAdaptation::class);
    }

    /**
     * Get users in this age group.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get content adaptation settings for a specific type.
     */
    public function getAdaptationFor($contentType)
    {
        return $this->contentAdaptations()
            ->where('content_type', $contentType)
            ->first();
    }

    /**
     * Check if an age falls within this age group.
     */
    public function isAgeInRange($age)
    {
        return $age >= $this->min_age && $age <= $this->max_age;
    }
}
