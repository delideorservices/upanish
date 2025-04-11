<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Monitoring;
use App\Models\User;
use App\Models\Session;
use App\Models\ProgressReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    /**
     * Get all students monitored by the authenticated user.
     */
    public function getStudents()
    {
        $user = Auth::user();
        
        // Check if user is a parent or teacher
        if (!in_array($user->role, ['parent', 'teacher'])) {
            return response()->json([
                'message' => 'Unauthorized. Only parents and teachers can monitor students.',
            ], 403);
        }
        
        // Get all monitored students with their basic info
        $students = $user->monitoredStudents()
            ->with('profile')
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'age' => $student->age,
                    'level' => $student->profile->current_level,
                    'total_points' => $student->profile->total_points,
                    'permission_level' => $student->pivot->permission_level,
                ];
            });
        
        return response()->json($students);
    }
    
    /**
     * Get progress data for a specific student.
     */
    public function getStudentProgress($studentId)
    {
        $user = Auth::user();
        
        // Check if the user has permission to monitor this student
        $monitoring = Monitoring::where('monitor_id', $user->id)
            ->where('student_id', $studentId)
            ->first();
            
        if (!$monitoring) {
            return response()->json([
                'message' => 'You do not have permission to monitor this student.',
            ], 403);
        }
        
        // Get the student
        $student = User::with('profile')->findOrFail($studentId);
        
        // Get summary of sessions by subject
        $subjectSummary = Session::where('user_id', $studentId)
            ->where('status', 'completed')
            ->select('subject_id', DB::raw('count(*) as session_count'), DB::raw('sum(points_earned) as total_points'))
            ->groupBy('subject_id')
            ->with('subject')
            ->get()
            ->map(function ($item) {
                return [
                    'subject_id' => $item->subject_id,
                    'subject_name' => $item->subject->name,
                    'session_count' => $item->session_count,
                    'total_points' => $item->total_points,
                ];
            });
        
        // Get recent achievements
        $recentAchievements = $student->achievements()
            ->orderBy('user_achievements.date_earned', 'desc')
            ->take(5)
            ->get()
            ->map(function ($achievement) {
                return [
                    'id' => $achievement->id,
                    'name' => $achievement->name,
                    'description' => $achievement->description,
                    'icon' => $achievement->icon,
                    'date_earned' => $achievement->pivot->date_earned,
                ];
            });
        
        // Get latest progress report
        $latestReport = ProgressReport::where('user_id', $studentId)
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Get recent activity (last 10 sessions)
        $recentActivity = Session::where('user_id', $studentId)
            ->with(['subject', 'topic'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'subject' => $session->subject->name,
                    'topic' => $session->topic->name,
                    'date' => $session->created_at,
                    'duration' => $session->duration,
                    'points_earned' => $session->points_earned,
                ];
            });
        
        // Return student progress data
        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->name,
                'age' => $student->age,
                'level' => $student->profile->current_level,
                'total_points' => $student->profile->total_points,
                'daily_streak' => $student->profile->daily_streak,
                'level_progress' => $student->profile->levelProgress(),
            ],
            'subject_summary' => $subjectSummary,
            'recent_achievements' => $recentAchievements,
            'progress_report' => $latestReport,
            'recent_activity' => $recentActivity,
        ]);
    }
    
    /**
     * Get session history for a specific student.
     */
    public function getStudentSessions($studentId)
    {
        $user = Auth::user();
        
        // Check if the user has permission to monitor this student
        $monitoring = Monitoring::where('monitor_id', $user->id)
            ->where('student_id', $studentId)
            ->first();
            
        if (!$monitoring) {
            return response()->json([
                'message' => 'You do not have permission to monitor this student.',
            ], 403);
        }
        
        // Get all sessions for the student
        $sessions = Session::where('user_id', $studentId)
            ->with(['subject', 'topic'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return response()->json($sessions);
    }
    
    /**
     * Get details for a specific session.
     */
    public function getStudentSession($studentId, $sessionId)
    {
        $user = Auth::user();
        
        // Check if the user has permission to monitor this student
        $monitoring = Monitoring::where('monitor_id', $user->id)
            ->where('student_id', $studentId)
            ->first();
            
        if (!$monitoring) {
            return response()->json([
                'message' => 'You do not have permission to monitor this student.',
            ], 403);
        }
        
        // Get the session with related data
        $session = Session::where('id', $sessionId)
            ->where('user_id', $studentId)
            ->with(['subject', 'topic', 'questions.responses'])
            ->firstOrFail();
        
        return response()->json($session);
    }
    
    /**
     * Generate a new progress report for a student.
     */
    public function generateProgressReport(Request $request, $studentId)
    {
        $user = Auth::user();
        
        // Check if the user has permission to monitor this student
        $monitoring = Monitoring::where('monitor_id', $user->id)
            ->where('student_id', $studentId)
            ->first();
            
        if (!$monitoring || !$monitoring->hasPermission('manage')) {
            return response()->json([
                'message' => 'You do not have permission to generate reports for this student.',
            ], 403);
        }
        
        $request->validate([
            'subject_id' => 'nullable|exists:subjects,id',
            'period_days' => 'nullable|integer|min:7|max:90',
        ]);
        
        $subjectId = $request->input('subject_id');
        $periodDays = $request->input('period_days', 30);
        
        // Get the student
        $student = User::findOrFail($studentId);
        
        // Generate the report
        $report = ProgressReport::generateFor($student, $subjectId, $periodDays);
        
        if (!$report) {
            return response()->json([
                'message' => 'Unable to generate report. Not enough data available for the selected period.',
            ], 400);
        }
        
        return response()->json([
            'message' => 'Progress report generated successfully.',
            'report' => $report,
        ]);
    }
    
    /**
     * Add a new student to monitor.
     */
    public function addStudent(Request $request)
    {
        $user = Auth::user();
        
        // Check if user is a parent or teacher
        if (!in_array($user->role, ['parent', 'teacher'])) {
            return response()->json([
                'message' => 'Unauthorized. Only parents and teachers can monitor students.',
            ], 403);
        }
        
        $request->validate([
            'student_email' => 'required|email|exists:users,email',
            'permission_level' => 'required|in:view,interact,manage',
            'notification_preferences' => 'nullable|json',
        ]);
        
        // Find the student by email
        $student = User::where('email', $request->student_email)
            ->where('role', 'student')
            ->first();
            
        if (!$student) {
            return response()->json([
                'message' => 'No student found with this email address.',
            ], 404);
        }
        
        // Check if already monitoring this student
        $existingMonitoring = Monitoring::where('monitor_id', $user->id)
            ->where('student_id', $student->id)
            ->first();
            
        if ($existingMonitoring) {
            return response()->json([
                'message' => 'You are already monitoring this student.',
            ], 400);
        }
        
        // Create the monitoring relationship
        $monitoring = Monitoring::create([
            'student_id' => $student->id,
            'monitor_id' => $user->id,
            'permission_level' => $request->permission_level,
            'notification_preferences' => $request->notification_preferences,
        ]);
        
        return response()->json([
            'message' => 'Student added to monitoring successfully.',
            'monitoring' => $monitoring,
        ]);
    }
    
    /**
     * Update monitoring settings for a student.
     */
    public function updateMonitoring(Request $request, $studentId)
    {
        $user = Auth::user();
        
        $request->validate([
            'permission_level' => 'required|in:view,interact,manage',
            'notification_preferences' => 'nullable|json',
        ]);
        
        // Find the monitoring relationship
        $monitoring = Monitoring::where('monitor_id', $user->id)
            ->where('student_id', $studentId)
            ->first();
            
        if (!$monitoring) {
            return response()->json([
                'message' => 'Monitoring relationship not found.',
            ], 404);
        }
        
        // Update the monitoring relationship
        $monitoring->update([
            'permission_level' => $request->permission_level,
            'notification_preferences' => $request->notification_preferences,
        ]);
        
        return response()->json([
            'message' => 'Monitoring settings updated successfully.',
            'monitoring' => $monitoring,
        ]);
    }
    
    /**
     * Remove a student from monitoring.
     */
    public function removeStudent($studentId)
    {
        $user = Auth::user();
        
        // Find and delete the monitoring relationship
        $deleted = Monitoring::where('monitor_id', $user->id)
            ->where('student_id', $studentId)
            ->delete();
            
        if (!$deleted) {
            return response()->json([
                'message' => 'Monitoring relationship not found.',
            ], 404);
        }
        
        return response()->json([
            'message' => 'Student removed from monitoring successfully.',
        ]);
    }
}
