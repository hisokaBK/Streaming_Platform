<?php

namespace App\Http\Middleware;

use App\Models\Comment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommentOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $comment = $request->route('comment');

        if (!$comment instanceof Comment) {
            $comment = Comment::findOrFail($comment);
        }

        if (!$request->user() || $request->user()->id !== $comment->user_id) {
            return response()->json([
                'message' => 'Unauthorized. You are not the owner of this comment.'
            ], 403);
        }

        return $next($request);
    }
}
