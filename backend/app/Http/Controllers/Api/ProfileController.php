<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileResource;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\Subscription;
use App\Models\User;

class ProfileController extends Controller
{
    public function showMyProfile(Request $request): JsonResponse
    {
        $authUser = $request->user();

        $profile = $authUser->profile()->with('user')->first();

        $profile->user->loadCount([
            'followerSubscriptions',
            'followingSubscriptions',
        ]);

        $followersPreview = Subscription::with('subscriber.profile')
            ->where('streamer_id', $authUser->id)
            ->latest()
            ->take(5)
            ->get();

        $followingPreview = Subscription::with('streamer.profile')
            ->where('subscriber_id', $authUser->id)
            ->latest()
            ->take(5)
            ->get();

        $videosPreview = $authUser->videos()
            ->withCount('comments')
            ->latest()
            ->take(5)
            ->get();

        $liveStream = $authUser->streams()
            ->withCount(['comments', 'reactions'])
            ->where('status', 'live')
            ->latest()
            ->first();

        $profile->followers_preview = $followersPreview;
        $profile->following_preview = $followingPreview;
        $profile->videos_preview = $videosPreview;
        $profile->live_stream = $liveStream;

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data' => [
                'profile' => new ProfileResource($profile),
            ],
        ]);
    }

    public function show(User $user, Request $request): JsonResponse
    {
        $profile = $user->profile()->with('user')->firstOrFail();

        $profile->user->loadCount([
            'followerSubscriptions',
            'followingSubscriptions',
        ]);

        $followersPreview = Subscription::with('subscriber.profile')
            ->where('streamer_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $followingPreview = Subscription::with('streamer.profile')
            ->where('subscriber_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $videosPreview = $user->videos()
            ->withCount('comments')
            ->latest()
            ->take(5)
            ->get();

        $liveStream = $user->streams()
            ->withCount(['comments', 'reactions'])
            ->where('status', 'live')
            ->latest()
            ->first();

        $profile->followers_preview = $followersPreview;
        $profile->following_preview = $followingPreview;
        $profile->videos_preview = $videosPreview;
        $profile->live_stream = $liveStream;

        return response()->json([
            'message' => 'Profile retrieved successfully',
            'data' => [
                'profile' => new ProfileResource($profile),
            ],
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $authUser = $request->user();

        $profile = $authUser->profile()->with('user')->first();

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('background_image')) {
            $data['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }

        $profile->update($data);

        $profile->load('user');

        $profile->user->loadCount([
            'followerSubscriptions',
            'followingSubscriptions',
        ]);

        $followersPreview = Subscription::with('subscriber.profile')
            ->where('streamer_id', $authUser->id)
            ->latest()
            ->take(5)
            ->get();

        $followingPreview = Subscription::with('streamer.profile')
            ->where('subscriber_id', $authUser->id)
            ->latest()
            ->take(5)
            ->get();

        $videosPreview = $authUser->videos()
            ->withCount('comments')
            ->latest()
            ->take(5)
            ->get();

        $liveStream = $authUser->streams()
            ->withCount(['comments', 'reactions'])
            ->where('status', 'live')
            ->latest()
            ->first();

        $profile->followers_preview = $followersPreview;
        $profile->following_preview = $followingPreview;
        $profile->videos_preview = $videosPreview;
        $profile->live_stream = $liveStream;

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => new ProfileResource($profile),
            ],
        ]);
    }
}
