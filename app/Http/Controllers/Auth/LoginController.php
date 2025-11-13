<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OmniUser;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Show login page.
     * If user already has session token, redirect to Home.vue.
     */
    public function showLogin(Request $request)
    {
        // Check if session token exists
        if (session()->has('auth_token') && session()->has('user_id')) {
            // ✅ Already logged in → redirect to /home
            return redirect()->route('home');
        }

        // Otherwise, show the Vue login page
        return view('login');
    }

    /**
     * Handle login API (for Vue frontend)
     */
    public function login(Request $request)
    {
        $user = OmniUser::where('usercode', $request->usercode)->first();

        if (!$user || $user->password !== $request->password) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        // ✅ Store login session
        session([
            'auth_token' => $token,
            'user_id' => $user->userid,
        ]);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        session()->forget(['auth_token', 'user_id']);
        $request->user()?->tokens()->delete();

        return redirect()->route('login');
    }

    /**
     * Return authenticated user info
     */
    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
