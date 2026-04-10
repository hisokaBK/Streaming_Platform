<?php

namespace App\Http\Middleware;

use App\Models\Stream;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsStreamOwner
{
    public function handle(Request $request, Closure $next): Response
    {

        $stream = Stream::findOrFail($stream);

        if (!$request->user() || $request->user()->id !== $stream->user_id) {
            return response()->json([
                'message' => 'Unauthorized. You are not the owner of this stream.'
            ], 403);
        }
        

        return $next($request);
    }
}
