<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'question_id',
        'content',
        'explanation_level',
        'created_by_agent',
        'helpful_rating',
    ];

    /**
     * Get the question that owns the response.
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Get the session via the question.
     */
    public function session()
    {
        return $this->question->session;
    }
}
