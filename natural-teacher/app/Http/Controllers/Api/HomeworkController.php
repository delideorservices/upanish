<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Response;
use App\Models\Session;
use App\Models\Subject;
use App\Models\Topic;
use App\Models\User;
use App\Services\AIService;
use App\Services\GamificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeworkController extends Controller
{
    protected $aiService;
    protected $gamificationService;
    
    public function __construct(AIService $aiService, GamificationService $gamificationService)
    {
        $this->aiService = $aiService;
        $this->gamificationService = $gamificationService;
    }
    
    /**
     * Submit a homework question and get AI assistance.
     */
    public function submit(Request $request)
    {
        $user = Auth::user();
        
        // Validate the request
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'topic_id' => 'required|exists:topics,id',
            'input_type' => 'required|in:text,photo',
            'content' => 'required_if:input_type,text',
            'file' => 'required_if:input_type,photo|file|image|max:10240',
        ]);
        
        // Get subject and topic
        $subject = Subject::findOrFail($request->subject_id);
        $topic = Topic::findOrFail($request->topic_id);
        
        // Create a new session
        $session = Session::create([
            'user_id' => $user->id,
            'subject_id' => $subject->id,
            'topic_id' => $topic->id,
            'start_time' => now(),
            'status' => 'active',
        ]);
        
        // Handle file upload if present
        $filePath = null;
        if ($request->input_type === 'photo' && $request->hasFile('file')) {
            $file = $request->file('file');
            $filename = Str::random(40) . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('homework', $filename, 'public');
        }
        
        // Create a new question
        $content = $request->input_type === 'text' 
            ? $request->content 
            : 'Uploaded image (see attachment)';
            
        $question = Question::create([
            'session_id' => $session->id,
            'content' => $content,
            'file_path' => $filePath,
            'complexity_level' => $topic->difficulty_level,
            'points_value' => $topic->points_available,
        ]);
        // dd($user->age);
        // Send to AI service
        $aiResponse = $this->aiService->analyzeHomework([
            'content' => $content,
            'file_path' => $filePath ? Storage::url($filePath) : null,
            'subject_id' => $subject->id,
            'student_age' => $user->age,
            'student_level' => $user->profile->current_level ?? 1,
            'learning_style' => $user->profile->preferred_learning_style ?? "visual",
            'session_id' => $session->id,
        ]);
        // dd($aiResponse);
        // Create response
        $response = Response::create([
            'question_id' => $question->id,
            'content' => $aiResponse['content'],
            'explanation_level' => $aiResponse['explanation_level'],
            'created_by_agent' => $aiResponse['created_by_agent'],
        ]);
        // Log::info('User after AI response', ['user' => Auth::user()]);

        // Award points
        $pointsEarned = $this->gamificationService->awardHomeworkPoints($user, $topic);
        
        // Update session
        $session->update([
            'end_time' => now(),
            'duration' => now()->diffInSeconds($session->start_time),
            'status' => 'completed',
            'points_earned' => $pointsEarned,
        ]);
        return response()->json([
            'success' => true,
            'response' => $aiResponse,
            'points_earned' => $pointsEarned,
            'session_id' => $session->id,
            'student_age' => $user->age,
            'student_level' => $user->profile->current_level ?? 1,
            'learning_style' => $user->profile->preferred_learning_style ?? 'visual',
        ]);
        
        
        // return response()->json([
        //     'success' => true,
        //     'response' =>$aiResponse,
        //     'points_earned' => $pointsEarned,
        // ]);
    }
    public function realTime(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'session_id' => 'nullable|exists:sessions,id',
        'subject_id' => 'required|exists:subjects,id',
        'topic_id' => 'required|exists:topics,id',
        'input_text' => 'required|string',
    ]);

    // Get or create session
    $session = $request->session_id 
        ? Session::findOrFail($request->session_id)
        : Session::create([
            'user_id' => $user->id,
            'subject_id' => $request->subject_id,
            'topic_id' => $request->topic_id,
            'start_time' => now(),
            'status' => 'active',
        ]);

    // Send current input + session to Flask
    $aiStep = $this->aiService->realTimeConversation([
        'input' => $request->input_text,
        'subject_id' => $request->subject_id,
        'topic_id' => $request->topic_id,
        'session_id' => $session->id,
        'student_age' => $user->age,
        'student_level' => $user->profile->current_level ?? 1,
        'learning_style' => $user->profile->preferred_learning_style ?? 'visual',
    ]);

    // Save question and response for tracking
    $question = Question::create([
        'session_id' => $session->id,
        'content' => $request->input_text,
        'complexity_level' => $session->topic->difficulty_level,
        'points_value' => $session->topic->points_available,
    ]);

    $response = Response::create([
        'question_id' => $question->id,
        'content' => $aiStep['reply'],
        'explanation_level' => $aiStep['level'] ?? null,
        'created_by_agent' => $aiStep['agent'] ?? 'english_teacher',
    ]);

    return response()->json([
        'success' => true,
        'session_id' => $session->id,
        'response' => $response->content,
    ]);
}
public function realTimeConversation(Request $request)
{
    $request->validate([
        'message' => 'required|string',
        'session_id' => 'required|integer|exists:sessions,id',
        'subject_id' => 'required|integer|exists:subjects,id',
        'student_age' => 'required|integer',
        'student_level' => 'required|integer',
        'learning_style' => 'required|string',
    ]);

    $payload = $request->only([
        'message',
        'session_id',
        'subject_id',
        'student_age',
        'student_level',
        'learning_style',
    ]);

    $response = $this->aiService->continueRealTimeFlow($payload);

    return response()->json([
        'success' => true,
        'response' => $response
    ]);
}

    /**
     * Provide feedback on a homework response.
     */
    public function feedback(Request $request)
    {
        $request->validate([
            'response_id' => 'required|exists:responses,id',
            'feedback_type' => 'required|in:very_helpful,somewhat_helpful,not_helpful',
        ]);
        
        $response = Response::findOrFail($request->response_id);
        
        // Check if user has permission to provide feedback
        $session = $response->question->session;
        if ($session->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You are not authorized to provide feedback for this response',
            ], 403);
        }
        
        // Update response with feedback
        $response->update([
            'helpful_rating' => $request->feedback_type,
        ]);
        
        // Optionally award bonus points for providing feedback
        if ($request->feedback_type === 'very_helpful') {
            $bonusPoints = $this->gamificationService->awardFeedbackPoints(Auth::user());
            
            return response()->json([
                'success' => true,
                'message' => 'Thank you for your feedback!',
                'bonus_points' => $bonusPoints,
            ]);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for your feedback!',
        ]);
    }
    
    /**
     * Get homework history for the authenticated user.
     */
    public function history()
    {
        $user = Auth::user();
        
        $sessions = Session::with(['subject', 'topic', 'questions.responses'])
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json($sessions);
    }
    
    /**
     * Get details for a specific homework session.
     */
    public function session($id)
    {
        $user = Auth::user();
        
        $session = Session::with(['subject', 'topic', 'questions.responses'])
            ->where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();
        
        return response()->json($session);
    }
}