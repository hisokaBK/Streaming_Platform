<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $profile = $request->route('profile');

        if (!$profile instanceof Profile) {
            $profile = Profile::findOrFail($profile);
        }

        if (!$request->user() || $request->user()->id !== $profile->user_id) {
            return response()->json([
                'message' => 'Unauthorized. You are not the owner of this profile.'
            ], 403);
        }

        return $next($request);
    }
}
