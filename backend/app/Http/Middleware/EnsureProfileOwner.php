<?php

namespace App\Http\Middleware;

use App\Models\Profile;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = $request->user();

        if (!$authUser->profile) {
            return response()->json([
                'message' => 'Profile not found.'
            ], 404);
        }

        $routeProfile = $request->route('profile');
        
        if ($routeProfile) {
            if (!$routeProfile instanceof Profile) {
                $routeProfile = Profile::find($routeProfile);
            }


            if ($routeProfile->user_id !== $authUser->id) {
                return response()->json([
                    'message' => 'Unauthorized. You are not the owner of this profile.'
                ], 403);
            }
        }



        return $next($request);
    }
}
