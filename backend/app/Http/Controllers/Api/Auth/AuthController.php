<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Profile;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\UserResource;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'role' => 'user',
        ]);

        Profile::create([
            'user_id' => $user->id,
            'avatar' => null,
            'background_image' => null,
            'bio' => null,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        $user->load('profile');

        return response()->json([
            'message' => 'Register successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (! Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = User::with('profile')->where('email', $credentials['email'])->first();

        if ($user->is_banned) {
            return response()->json([
                'message' => 'Your account is banned',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    


}
