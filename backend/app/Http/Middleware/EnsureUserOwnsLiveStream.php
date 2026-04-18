<?php

namespace App\Http\Middleware;

use App\Models\Stream;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserOwnsLiveStream
{
    public function handle(Request $request, Closure $next): Response
    {
        $stream = $request->route('stream');

        if (!$stream instanceof Stream) {
            $stream = Stream::findOrFail($stream);
        }

        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized.'
            ], 401);
        }

        if ((int) $request->user()->id !== (int) $stream->user_id) {
            return response()->json([
                'message' => 'Unauthorized. You are not the owner of this stream.'
            ], 403);
        }

        if ($stream->status !== 'live') {
            return response()->json([
                'message' => 'Only live streams can be edited.'
            ], 403);
        }

        return $next($request);
    }
}
