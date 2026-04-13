<?php

namespace App\Http\Controllers\Api;

use App\Models\Comment;
use App\Models\Stream;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Requests\Comment\UpdateCommentRequest;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $stream = Stream::findOrFail($request->stream_id);

        if ($stream->status !== 'live') {
            return response()->json([
                'message' => 'Comments can only be added to live streams.'
            ], 422);
        }

        $comment = Comment::create([
            'user_id' => auth()->id(),
            'stream_id' => $stream->id,
            'video_id' => null,
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => new CommentResource($comment),
        ], 201);
    }

    public function update(UpdateCommentRequest $request, Comment $comment): JsonResponse
    {
        $comment->update([
            'content' => $request->content,
        ]);

        $comment->load('user');

        return response()->json([
            'message' => 'Comment updated successfully.',
            'data' => new CommentResource($comment),
        ]);
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully.',
        ]);
    }
}
