<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeworkController;
use App\Http\Controllers\Api\GamificationController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\MonitoringController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // User info
    Route::get('/user', function (Request $request) {
        return $request->user()->load('profile');
    });
    
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // Subjects and topics
    Route::get('/subjects', [SubjectController::class, 'index']);
    Route::get('/subjects/{id}', [SubjectController::class, 'show']);
    Route::get('/topics', [TopicController::class, 'index']);
    Route::get('/topics/{id}', [TopicController::class, 'show']);
    Route::get('/subjects/{id}/topics', [TopicController::class, 'bySubject']);
    
    // Homework
    Route::post('/homework/submit', [HomeworkController::class, 'submit']);
    Route::post('/homework/feedback', [HomeworkController::class, 'feedback']);
    Route::get('/homework/history', [HomeworkController::class, 'history']);
    Route::get('/homework/session/{id}', [HomeworkController::class, 'session']);
    Route::post('/homework/real-time-conversation', [HomeworkController::class, 'realTimeConversation']);

    
    // Gamification
    Route::get('/gamification/user-data', [GamificationController::class, 'getUserData']);
    Route::post('/gamification/check-achievements', [GamificationController::class, 'checkAchievements']);
    Route::post('/gamification/redeem-reward/{id}', [GamificationController::class, 'redeemReward']);
    Route::get('/gamification/leaderboard', [GamificationController::class, 'getLeaderboard']);
    
    // Monitoring (Parent/Teacher only)
    Route::middleware('role:parent,teacher')->group(function () {
        Route::get('/monitoring/students', [MonitoringController::class, 'getStudents']);
        Route::get('/monitoring/student/{id}/progress', [MonitoringController::class, 'getStudentProgress']);
        Route::get('/monitoring/student/{id}/sessions', [MonitoringController::class, 'getStudentSessions']);
        Route::get('/monitoring/student/{id}/session/{sessionId}', [MonitoringController::class, 'getStudentSession']);
    });
});