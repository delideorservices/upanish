<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentAdaptation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'age_group_id',
        'content_type',
        'vocabulary_limit',
        'sentence_length',
        'use_illustrations',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'use_illustrations' => 'boolean',
    ];

    /**
     * Get the age group that owns this adaptation.
     */
    public function ageGroup()
    {
        return $this->belongsTo(AgeGroup::class);
    }
}
