<?php

namespace App\Http\Controllers;

use App\Models\OmniUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
    
        $validated = $request->validate([
            'username' => 'required|string|max:200|unique:omni_user,username',
            'email'    => 'required|string|email|max:200|unique:omni_user,user_email',
            'password' => 'required|string|min:6'
        ]);

        $user = OmniUser::create([
            'username'      => $validated['username'],
            'user_email'    => $validated['email'],
            'usercode'      => $validated['username'], // or generate some code
            'user_type'     => 1,
            'status'        => 1,
            'password'      => Hash::make($validated['password']),
            'created_date'  => now(),
            'created_by'    => 0,
            'modified_date' => now(),
            'modified_by'   => 0,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => [
                'id'       => $user->userid,
                'username' => $user->username,
                'email'    => $user->user_email,
            ],
        ], 201);
    }

    /**
     * Login and issue Sanctum token
     */
    public function login(Request $request)
    {
        $request->validate([
            'username'    => 'required|string',
            'password' => 'required|string',
        ]);

        $user = OmniUser::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        session(['auth_token' => $token, 'user_id' => $user->userid]);

        return response()->json([
            'token' => $token,
            'user'  => [
                'id'       => $user->userid,
                'username' => $user->username,
                'email'    => $user->user_email,
            ]
        ]);
    }

    /**
     * Return the authenticated user
     */
    public function me(Request $request)
    {
        \Log::info('🔎 Authenticated user via /api/me:', ['user' => $request->user()]);
    return response()->json([
        'id'       => $request->user()?->userid,
        'username' => $request->user()?->username,
        'email'    => $request->user()?->user_email,
    ]);
}

    /**
     * Logout (revoke current token)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }
}
