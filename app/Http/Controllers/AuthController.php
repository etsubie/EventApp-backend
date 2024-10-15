<?php

namespace App\Http\Controllers;

use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            "name" => "required|max:255",
            "email" => "required|email|unique:users",
            "password" => "required|confirmed",
            "role" => "required|in:host,attendee"
        ]);
        if ($data) {
            $user = User::create($data);

            $user->assignRole($data['role']);
            $token = $user->createToken($request->name);
            return [
                'user' => $user,
                'token' => $token->plainTextToken,
                'role' => $user->getRoleNames()->isNotEmpty() ? $user->getRoleNames()[0] : null,
            ];
        }


    }
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|exists:users,email",
            "password" => "required"
        ]);

        // Retrieve user
        $user = User::where("email", $request->email)->first();

        // Check password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                "errors" => [
                    "email" => ["Invalid Credentials"]
                ]
            ], 401);
        }

        // Generate token after successful login
        $token = $user->createToken($user->name);

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
            'role' => $user->getRoleNames()->isNotEmpty() ? $user->getRoleNames()[0] : null,
        ];
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ['message' => 'You are Logged out'];
    }
}