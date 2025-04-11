<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressReport extends Model
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
        'strengths',
        'areas_for_improvement',
        'recommendations',
        'period_start',
        'period_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    /**
     * Get the user for this progress report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the subject for this progress report.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Generate a new progress report for a user.
     */
    public static function generateFor(User $user, $subjectId = null, $periodDays = 30)
    {
        $endDate = now();
        $startDate = now()->subDays($periodDays);
        
        // Get sessions for the period
        $sessionsQuery = Session::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate]);
            
        if ($subjectId) {
            $sessionsQuery->where('subject_id', $subjectId);
        }
        
        $sessions = $sessionsQuery->get();
        
        // If no sessions, return null
        if ($sessions->isEmpty()) {
            return null;
        }
        
        // Calculate strengths and areas for improvement
        $subjectPerformance = [];
        $topicPerformance = [];
        
        foreach ($sessions as $session) {
            $subjectId = $session->subject_id;
            $topicId = $session->topic_id;
            
            // Track session count by subject
            if (!isset($subjectPerformance[$subjectId])) {
                $subjectPerformance[$subjectId] = [
                    'session_count' => 0,
                    'points_earned' => 0,
                ];
            }
            
            $subjectPerformance[$subjectId]['session_count']++;
            $subjectPerformance[$subjectId]['points_earned'] += $session->points_earned;
            
            // Track session count by topic
            if (!isset($topicPerformance[$topicId])) {
                $topicPerformance[$topicId] = [
                    'session_count' => 0,
                    'points_earned' => 0,
                ];
            }
            
            $topicPerformance[$topicId]['session_count']++;
            $topicPerformance[$topicId]['points_earned'] += $session->points_earned;
        }
        
        // Identify strengths (top 3 topics by points)
        arsort($topicPerformance);
        $strengths = array_slice(array_keys($topicPerformance), 0, 3);
        $strengthsTopics = Topic::whereIn('id', $strengths)->get();
        $strengthsText = "Good performance in: " . $strengthsTopics->pluck('name')->join(', ');
        
        // Identify areas for improvement (topics with less than 2 sessions)
        $areasForImprovement = [];
        foreach ($topicPerformance as $topicId => $data) {
            if ($data['session_count'] < 2) {
                $areasForImprovement[] = $topicId;
            }
        }
        
        $improvementTopics = Topic::whereIn('id', $areasForImprovement)->get();
        $improvementText = "Needs more practice in: " . $improvementTopics->pluck('name')->join(', ');
        
        // Generate recommendations
        $recommendations = "Recommended focus areas:\n";
        foreach ($improvementTopics as $topic) {
            $recommendations .= "- Practice more {$topic->name} problems\n";
        }
        
        $recommendations .= "\nComplete at least 3 sessions per week to maintain progress.";
        
        // Create the progress report
        return self::create([
            'user_id' => $user->id,
            'subject_id' => $subjectId,
            'strengths' => $strengthsText,
            'areas_for_improvement' => $improvementText,
            'recommendations' => $recommendations,
            'period_start' => $startDate,
            'period_end' => $endDate,
        ]);
    }
}
