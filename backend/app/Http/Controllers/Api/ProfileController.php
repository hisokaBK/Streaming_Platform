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
        $profile = $authUser->profile()->with('user')->firstOrFail();

        $this->hydrateProfilePayload($profile, $authUser);

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

        $this->hydrateProfilePayload($profile, $user);

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
        $profile = $authUser->profile()->with('user')->firstOrFail();

        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if ($request->hasFile('background_image')) {
            $data['background_image'] = $request->file('background_image')->store('backgrounds', 'public');
        }

        $profile->update($data);
        $profile->load('user');

        $this->hydrateProfilePayload($profile, $authUser);

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => [
                'profile' => new ProfileResource($profile),
            ],
        ]);
    }

    private function hydrateProfilePayload($profile, User $user): void
    {
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
            ->with([
                'categories',
                'stream',
            ])
            ->withCount('comments')
            ->where('recording_status', 'completed')
            ->whereNotNull('url')
            ->latest('recorded_at')
            ->latest()
            ->take(12)
            ->get();

        $liveStream = $user->streams()
            ->withCount(['comments', 'reactions'])
            ->where('status', 'live')
            ->latest()
            ->first();

        $streamsCount = $user->streams()->count();

        $videosCount = $user->videos()
            ->where('recording_status', 'completed')
            ->whereNotNull('url')
            ->count();

        $mostReactedVideo = $user->videos()
            ->with([
                'stream',
                'categories',
            ])
            ->withCount('comments')
            ->where('recording_status', 'completed')
            ->whereNotNull('url')
            ->get()
            ->sortByDesc(function ($video) {
                return $video->stream?->reactions()->count() ?? 0;
            })
            ->first();

        if ($mostReactedVideo && $mostReactedVideo->stream) {
            $mostReactedVideo->stream->loadCount('reactions');
        }

        $profile->followers_preview = $followersPreview;
        $profile->following_preview = $followingPreview;
        $profile->videos_preview = $videosPreview;
        $profile->live_stream = $liveStream;
        $profile->streams_count = $streamsCount;
        $profile->videos_count = $videosCount;
        $profile->most_reacted_video = $mostReactedVideo;
    }
}
