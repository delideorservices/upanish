<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Question extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'session_id',
        'content',
        'file_path',
        'complexity_level',
        'points_value',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'file_url',
    ];

    /**
     * Get the session that owns the question.
     */
    public function session()
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Get the responses for this question.
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Get the file URL attribute.
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path) {
            return Storage::url($this->file_path);
        }

        return null;
    }
}
