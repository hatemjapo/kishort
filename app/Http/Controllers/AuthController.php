<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        if (! config('app.registration_enabled')) {
            return response()->json([
                'message' => 'Registration is currently disabled.',
                ], 403);
        }

        $validatedRequest = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $user = User::create([
            'name' => $validatedRequest['name'],
            'email' => $validatedRequest['email'],
            'password' => Hash::make($validatedRequest['password']),
        ]);

        $token = $this->generateToken($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validatedRequest = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validatedRequest['email'])->first();

        if ($user === null || ! Hash::check($validatedRequest['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Wrong Email or Password'],
            ]);
        }

        $token = $this->generateToken($user);

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'logged out']);
    }

    protected function generateToken(User $user)
    {
        return $user->createToken('api-token')->plainTextToken;
    }
}
