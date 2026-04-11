<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\StoreSubscriptionRequest;

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
        $followers = Subscription::with('subscriber')
            ->where('streamer_id', $user->id)
            ->latest()
            ->paginate(10);

        $data = $followers->through(function ($subscription) {
            return [
                'subscription_id' => $subscription->id,
                'id' => $subscription->subscriber?->id,
                'name' => $subscription->subscriber?->name,
                'email' => $subscription->subscriber?->email,
                'followed_at' => $subscription->created_at,
            ];
        });

        return response()->json($data);
    }

    public function following(User $user): JsonResponse
    {
        $following = Subscription::with('streamer')
            ->where('subscriber_id', $user->id)
            ->latest()
            ->paginate(10);

        $data = $following->through(function ($subscription) {
            return [
                'subscription_id' => $subscription->id,
                'id' => $subscription->streamer?->id,
                'name' => $subscription->streamer?->name,
                'email' => $subscription->streamer?->email,
                'followed_at' => $subscription->created_at,
            ];
        });

        return response()->json($data);
    }
}
