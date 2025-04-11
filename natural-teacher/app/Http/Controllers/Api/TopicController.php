<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TopicController extends Controller
{
     /**
     * Get all active topics.
     */
    public function index()
    {
        $topics = Topic::with('subject')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return response()->json($topics);
    }
    
    /**
     * Get a specific topic.
     */
    public function show($id)
    {
        $topic = Topic::with('subject')->findOrFail($id);
        
        return response()->json($topic);
    }
    
    /**
     * Get topics for a specific subject.
     */
    public function bySubject($subjectId)
    {
        $subject = Subject::findOrFail($subjectId);
        
        $topics = $subject->topics()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return response()->json($topics);
    }
    
    /**
     * Get topics appropriate for the authenticated user's age.
     */
    public function forCurrentUser()
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            // For non-students, return all topics
            return $this->index();
        }
        
        $topics = Topic::with('subject')
            ->where('is_active', true)
            ->where('age_group_min', '<=', $user->age)
            ->where('age_group_max', '>=', $user->age)
            ->orderBy('subject_id')
            ->orderBy('name')
            ->get();
            
        return response()->json($topics);
    }
    
    /**
     * Get topics by difficulty level.
     */
    public function byDifficulty($level)
    {
        $topics = Topic::with('subject')
            ->where('is_active', true)
            ->where('difficulty_level', $level)
            ->orderBy('subject_id')
            ->orderBy('name')
            ->get();
            
        return response()->json($topics);
    }
}