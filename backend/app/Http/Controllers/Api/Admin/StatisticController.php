<?php

namespace App\Http\Controllers\Api\Admin;

use App\Models\User;
use App\Models\Video;
use App\Models\Stream;
use App\Models\Comment;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminStatisticResource;

class StatisticController extends Controller
{
    public function dashboard(): JsonResponse
    {
        $totalUsers = User::count();
        $bannedUsers = User::where('is_banned', true)->count();
        $activeUsers = $totalUsers - $bannedUsers;

        $totalStreams = Stream::count();
        $liveStreams = Stream::where('status', 'live')->count();
        $endedStreams = Stream::where('status', 'ended')->count();

        $totalVideos = Video::count();

        $totalComments = Comment::count();
        $totalReactions = Reaction::count();
        $totalSubscriptions = Subscription::count();
        $totalMessages = Message::count();

        $mostReactedStream = Stream::withCount('reactions')
            ->orderByDesc('reactions_count')
            ->first();

        $mostCommentedStream = Stream::withCount('comments')
            ->orderByDesc('comments_count')
            ->first();

        $mostReactedVideo = Video::with([
                'stream' => function ($query) {
                    $query->withCount('reactions');
                }
            ])
            ->get()
            ->sortByDesc(function ($video) {
                return $video->stream?->reactions_count ?? 0;
            })
            ->first();

        $mostCommentedVideo = Video::withCount('comments')
            ->orderByDesc('comments_count')
            ->first();

        $statistics = [
            'users' => [
                'total_users' => $totalUsers,
                'banned_users' => $bannedUsers,
                'active_users' => $activeUsers,
            ],

            'streams' => [
                'total_streams' => $totalStreams,
                'live_streams' => $liveStreams,
                'ended_streams' => $endedStreams,
            ],

            'videos' => [
                'total_videos' => $totalVideos,
            ],

            'engagement' => [
                'total_comments' => $totalComments,
                'total_reactions' => $totalReactions,
                'total_subscriptions' => $totalSubscriptions,
                'total_messages' => $totalMessages,
            ],

            'top_stats' => [
                'most_reacted_stream' => $mostReactedStream ? [
                    'id' => $mostReactedStream->id,
                    'title' => $mostReactedStream->title,
                    'reactions_count' => $mostReactedStream->reactions_count,
                ] : null,

                'most_commented_stream' => $mostCommentedStream ? [
                    'id' => $mostCommentedStream->id,
                    'title' => $mostCommentedStream->title,
                    'comments_count' => $mostCommentedStream->comments_count,
                ] : null,

                'most_reacted_video' => $mostReactedVideo ? [
                    'id' => $mostReactedVideo->id,
                    'title' => $mostReactedVideo->title,
                    'reactions_count' => $mostReactedVideo->stream?->reactions_count ?? 0,
                ] : null,

                'most_commented_video' => $mostCommentedVideo ? [
                    'id' => $mostCommentedVideo->id,
                    'title' => $mostCommentedVideo->title,
                    'comments_count' => $mostCommentedVideo->comments_count,
                ] : null,
            ],
        ];

        return response()->json([
            'message' => 'Admin statistics retrieved successfully.',
            'data' => new AdminStatisticResource($statistics),
        ]);
    }
}
