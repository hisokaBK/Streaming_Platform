<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasNoLiveStream
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $hasLiveStream = $user->streams()
            ->where('status', 'live')
            ->exists();

        if ($hasLiveStream) {
            return response()->json([
                'message' => 'You already have a live stream.',
            ], 422);
        }

        return $next($request);
    }
}
