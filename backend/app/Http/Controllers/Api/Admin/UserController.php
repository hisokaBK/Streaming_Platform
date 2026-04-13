<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $users = User::with('profile')
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Users retrieved successfully.',
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function ban(User $user): JsonResponse
    {
        if ($user->is_banned) {
            return response()->json([
                'message' => 'User is already banned.',
            ], 422);
        }

        $user->update([
            'is_banned' => true,
        ]);

        $user->load('profile');

        return response()->json([
            'message' => 'User banned successfully.',
            'data' => new UserResource($user),
        ]);
    }

    public function unban(User $user): JsonResponse
    {
        if (!$user->is_banned) {
            return response()->json([
                'message' => 'User is not banned.',
            ], 422);
        }

        $user->update([
            'is_banned' => false,
        ]);

        $user->load('profile');

        return response()->json([
            'message' => 'User unbanned successfully.',
            'data' => new UserResource($user),
        ]);
    }
}
