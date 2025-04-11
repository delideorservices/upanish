<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Monitoring extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'monitoring';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'student_id',
        'monitor_id',
        'permission_level',
        'notification_preferences',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'notification_preferences' => 'json',
    ];

    /**
     * Get the student being monitored.
     */
    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Get the monitor (parent or teacher).
     */
    public function monitor()
    {
        return $this->belongsTo(User::class, 'monitor_id');
    }

    /**
     * Check if monitor has specific permission.
     */
    public function hasPermission($permission)
    {
        if ($this->permission_level === 'manage') {
            return true;
        }
        
        if ($this->permission_level === 'interact' && in_array($permission, ['view', 'interact'])) {
            return true;
        }
        
        if ($this->permission_level === 'view' && $permission === 'view') {
            return true;
        }
        
        return false;
    }
}
