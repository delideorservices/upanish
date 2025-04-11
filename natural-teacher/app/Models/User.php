<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'age',
        'age_group_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get the profile associated with the user.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Get the age group associated with the user.
     */
    public function ageGroup()
    {
        return $this->belongsTo(AgeGroup::class);
    }

    /**
     * Get the sessions associated with the user.
     */
    public function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Get the achievements earned by the user.
     */
    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'user_achievements')
            ->withPivot('date_earned')
            ->withTimestamps();
    }

    /**
     * Get the badges earned by the user.
     */
    public function badges()
    {
        return $this->belongsToMany(Badge::class, 'user_badges')
            ->withPivot('date_earned')
            ->withTimestamps();
    }

    /**
     * Get the rewards earned by the user.
     */
    public function rewards()
    {
        return $this->belongsToMany(Reward::class, 'user_rewards')
            ->withPivot('is_redeemed', 'redemption_date')
            ->withTimestamps();
    }

    /**
     * Get the challenges participated in by the user.
     */
    public function challenges()
    {
        return $this->belongsToMany(Challenge::class, 'user_challenges')
            ->withPivot('progress_percent', 'completed_date', 'points_earned')
            ->withTimestamps();
    }

    /**
     * Get monitored students (for parent/teacher roles).
     */
    public function monitoredStudents()
    {
        return $this->belongsToMany(User::class, 'monitoring', 'monitor_id', 'student_id')
            ->withPivot('permission_level', 'notification_preferences')
            ->withTimestamps();
    }

    /**
     * Get monitors (for student role).
     */
    public function monitors()
    {
        return $this->belongsToMany(User::class, 'monitoring', 'student_id', 'monitor_id')
            ->withPivot('permission_level', 'notification_preferences')
            ->withTimestamps();
    }

    /**
     * Check if the user is a student.
     */
    public function isStudent()
    {
        return $this->role === 'student';
    }

    /**
     * Check if the user is a parent.
     */
    public function isParent()
    {
        return $this->role === 'parent';
    }

    /**
     * Check if the user is a teacher.
     */
    public function isTeacher()
    {
        return $this->role === 'teacher';
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}