<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Government;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class GovernmentAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'string',
            'email' => 'required|email|unique:governments',
            'password' => 'required|min:8',
        ]);

        $government = Government::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $government->createToken('government-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Government user registered successfully',
            'data' => [
                'government' => $government,
                'token' => $token
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $government = Government::where('email', $request->email)->first();

        if (!$government || !Hash::check($request->password, $government->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $government->createToken('government-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Government user logged in successfully',
            'data' => [
                  'government' => [
                    ...$government->toArray(),
                    'role' => 'Government'
                ],
                'token' => $token
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Government user logged out successfully'
        ]);
    }
} 