<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgeGroup;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Carbon\Carbon;
class AuthController extends Controller
{
    /**
     * Handle user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;
            
            // Update daily streak if user is a student
            if ($user->role === 'student' && $user->profile) {
                $this->updateStreak($user->profile);
            }
            
            return response()->json([
                'user' => $user->load('profile'),
                'token' => $token,
            ]);
        }

        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Handle user registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:student,parent,teacher',
            'age' => 'required_if:role,student|nullable|integer|min:5|max:15',
            'learning_style' => 'required_if:role,student|nullable|in:visual,auditory,reading,kinesthetic,mixed',
        ]);

        // Determine age group for students
        $ageGroupId = null;
        if ($request->role === 'student' && $request->age) {
            try {
                $ageGroup = AgeGroup::where('min_age', '<=', $request->age)
                    ->where('max_age', '>=', $request->age)
                    ->firstOrFail();
                $ageGroupId = $ageGroup->id;
            } catch (ModelNotFoundException $e) {
                // If no matching age group, create a default one
                $ageGroupId = $this->createDefaultAgeGroup($request->age);
            }
        }

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'age' => $request->age,
            'age_group_id' => $ageGroupId,
        ]);

        // Create profile based on role
        if ($request->role === 'student') {
            $user->profile()->create([
                'preferred_learning_style' => $request->learning_style ?? 'mixed',
                'current_level' => 1,
                'total_points' => 0,
                'daily_streak' => 1,
                'last_login_date' => now(),
            ]);
        } else {
            $user->profile()->create([
                'current_level' => 1,
                'total_points' => 0,
            ]);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load('profile'),
            'token' => $token,
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Update user streak.
     *
     * @param  \App\Models\Profile  $profile
     * @return void
     */
    private function updateStreak($profile)
    {
        $today = now()->format('Y-m-d');
        $lastLogin = $profile->last_login_date 
            ? Carbon::parse($profile->last_login_date) 
            : null;
    
        if (!$lastLogin) {
            $profile->daily_streak = 1;
        } elseif (now()->subDay()->format('Y-m-d') === $lastLogin->format('Y-m-d')) {
            $profile->daily_streak += 1;
        } elseif ($lastLogin->format('Y-m-d') !== $today) {
            $profile->daily_streak = 1;
        }
    
        $profile->last_login_date = now();
        $profile->save();
    }
    
    /**
     * Create a default age group if none exists for the given age.
     *
     * @param  int  $age
     * @return int
     */
    private function createDefaultAgeGroup($age)
    {
        if ($age >= 5 && $age <= 7) {
            $name = 'Early Elementary';
            $min = 5;
            $max = 7;
            $level = 1;
        } elseif ($age >= 8 && $age <= 10) {
            $name = 'Elementary';
            $min = 8;
            $max = 10;
            $level = 2;
        } else {
            $name = 'Middle School';
            $min = 11;
            $max = 15;
            $level = 3;
        }

        $ageGroup = AgeGroup::create([
            'name' => $name,
            'min_age' => $min,
            'max_age' => $max,
            'complexity_level' => $level,
            'vocabulary_level' => $level === 1 ? 'basic' : ($level === 2 ? 'intermediate' : 'advanced'),
        ]);

        return $ageGroup->id;
    }
}
