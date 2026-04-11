<?php

namespace App\Http\Middleware;

use App\Models\Video;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureVideoOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $video = $request->route('video');

        if (!$video instanceof Video) {
            $video = Video::findOrFail($video);
        }

        if (!$request->user() || $request->user()->id !== $video->user_id) {
            return response()->json([
                'message' => 'Unauthorized. You are not the owner of this video.'
            ], 403);
        }

        return $next($request);
    }
}
