<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubjectController extends Controller
{
    /**
     * Get all active subjects.
     */
    public function index()
    {
        $subjects = Subject::where('is_active', true)
            ->orderBy('display_order')
            ->get();
            
        return response()->json($subjects);
    }
    
    /**
     * Get a specific subject.
     */
    public function show($id)
    {
        $subject = Subject::findOrFail($id);
        
        return response()->json($subject);
    }
    
    /**
     * Get subjects appropriate for the authenticated user's age.
     */
    public function forCurrentUser()
    {
        $user = Auth::user();
        
        if ($user->role !== 'student') {
            // For non-students, return all subjects
            return $this->index();
        }
        
        $subjects = Subject::where('is_active', true)
            ->whereHas('topics', function ($query) use ($user) {
                $query->where('age_group_min', '<=', $user->age)
                    ->where('age_group_max', '>=', $user->age)
                    ->where('is_active', true);
            })
            ->orderBy('display_order')
            ->get();
            
        return response()->json($subjects);
    }
}