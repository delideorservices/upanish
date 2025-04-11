<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UiTheme extends Model
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
        'primary_color',
        'secondary_color',
        'accent_color',
        'font_family',
        'age_group_target',
        'is_default',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
    ];

    /**
     * Get the age groups that use this theme.
     */
    public function ageGroups()
    {
        return $this->hasMany(AgeGroup::class, 'default_theme_id');
    }

    /**
     * Get CSS variables for this theme.
     */
    public function getCssVariables()
    {
        return [
            '--primary-color' => $this->primary_color,
            '--secondary-color' => $this->secondary_color,
            '--accent-color' => $this->accent_color,
            '--font-family' => $this->font_family,
        ];
    }
}
