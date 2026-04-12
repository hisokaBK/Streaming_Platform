<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Resources\ProfileResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $profile = $request->user()->profile()->with('user')->first();

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data' => [
                'profile' => new ProfileResource($profile),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile;
    
        $data = $request->validated();
    
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
    
        if ($request->hasFile('background_image')) {
            $data['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }
    
        $profile->update($data);
        $profile->load('user');
    
        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => new ProfileResource($profile),
            ],
        ]);
    }
}
