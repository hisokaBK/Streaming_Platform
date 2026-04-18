<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;
use App\Http\Resources\SubscriptionUserResource;

class SubscriptionController extends Controller
{
    public function follow(StoreSubscriptionRequest $request): JsonResponse
    {
        $subscriberId = auth()->id();
        $streamerId = (int) $request->streamer_id;

        if ($subscriberId === $streamerId) {
            return response()->json([
                'message' => 'You cannot follow yourself.'
            ], 422);
        }

        $streamer = User::findOrFail($streamerId);

        $subscription = Subscription::firstOrCreate([
            'subscriber_id' => $subscriberId,
            'streamer_id' => $streamer->id,
        ]);

        return response()->json([
            'message' => 'Followed successfully.',
            'data' => [
                'id' => $subscription->id,
                'subscriber_id' => $subscription->subscriber_id,
                'streamer_id' => $subscription->streamer_id,
            ],
        ], 201);
    }

    public function unfollow(User $user): JsonResponse
    {
        $subscription = Subscription::where('subscriber_id', auth()->id())
            ->where('streamer_id', $user->id)
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => 'Subscription not found.'
            ], 404);
        }

        $subscription->delete();

        return response()->json([
            'message' => 'Unfollowed successfully.',
        ]);
    }

    public function followers(User $user): JsonResponse
    {
        $followers = Subscription::with('subscriber.profile')
            ->where('streamer_id', $user->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Followers retrieved successfully.',
            'data' => FollowUserResource::collection($followers),
            'meta' => [
                'current_page' => $followers->currentPage(),
                'last_page' => $followers->lastPage(),
                'per_page' => $followers->perPage(),
                'total' => $followers->total(),
            ],
        ]);
    }

    public function following(User $user): JsonResponse
    {
        $following = Subscription::with('streamer.profile')
            ->where('subscriber_id', $user->id)
            ->latest()
            ->paginate(10);

        return response()->json([
            'message' => 'Following retrieved successfully.',
            'data' => FollowUserResource::collection($following),
            'meta' => [
                'current_page' => $following->currentPage(),
                'last_page' => $following->lastPage(),
                'per_page' => $following->perPage(),
                'total' => $following->total(),
            ],
        ]);
    }
}
