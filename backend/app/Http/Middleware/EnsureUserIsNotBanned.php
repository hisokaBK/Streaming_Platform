<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotBanned
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user->is_banned) {
            return response()->json([
                'message' => 'Your account has been banned. You cannot access the system.'
            ], 403);
        }

        return $next($request);
    }
}
