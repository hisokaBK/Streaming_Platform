<?php

namespace App\Http\Controllers\Api;

use App\Models\Stream;
use App\Models\Reaction;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
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
    
        return response()->json([
            'message' => 'Reaction added successfully.',
            'data' => new ReactionResource($reaction),
        ], 201);
    }
}
