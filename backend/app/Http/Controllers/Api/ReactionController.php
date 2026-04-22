<?php

namespace App\Http\Controllers\Api;

use App\Events\ReactionUpdated;
use App\Models\Stream;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReactionResource;
use App\Http\Requests\Reaction\StoreReactionRequest;

class ReactionController extends Controller
{
    public function store(StoreReactionRequest $request): JsonResponse
    {
        $stream = Stream::findOrFail($request->stream_id);

        if ($stream->status !== 'live') {
            return response()->json([
                'message' => 'Reactions can only be added to live streams.',
            ], 422);
        }

        $reaction = Reaction::create([
            'user_id' => auth()->id(),
            'stream_id' => $stream->id,
            'type' => $request->type,
        ]);

        $stream->load('reactions')->loadCount('reactions');

        $reactionTypes = ['like', 'love', 'haha', 'wow', 'sad', 'angry', 'clap', 'fire'];

        $summary = collect($reactionTypes)->mapWithKeys(function ($type) use ($stream) {
            return [
                $type => $stream->reactions->where('type', $type)->count(),
            ];
        })->toArray();

        broadcast(new ReactionUpdated(
            (int) $stream->id,
            $summary,
            (int) $stream->reactions_count
        ))->toOthers();

        return response()->json([
            'message' => 'Reaction added successfully.',
            'data' => new ReactionResource($reaction),
        ], 201);
    }
}
