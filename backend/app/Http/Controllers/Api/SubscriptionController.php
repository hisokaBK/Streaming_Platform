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

   




}
