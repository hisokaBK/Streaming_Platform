<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminStatisticResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'users' => [
                'total_users' => $this['users']['total_users'] ?? 0,
                'banned_users' => $this['users']['banned_users'] ?? 0,
                'active_users' => $this['users']['active_users'] ?? 0,
            ],

            'streams' => [
                'total_streams' => $this['streams']['total_streams'] ?? 0,
                'live_streams' => $this['streams']['live_streams'] ?? 0,
                'ended_streams' => $this['streams']['ended_streams'] ?? 0,
            ],

            'videos' => [
                'total_videos' => $this['videos']['total_videos'] ?? 0,
            ],

            'engagement' => [
                'total_comments' => $this['engagement']['total_comments'] ?? 0,
                'total_reactions' => $this['engagement']['total_reactions'] ?? 0,
                'total_subscriptions' => $this['engagement']['total_subscriptions'] ?? 0,
                'total_messages' => $this['engagement']['total_messages'] ?? 0,
            ],

            'top_stats' => [
                'most_reacted_stream' => $this['top_stats']['most_reacted_stream'] ?? null,
                'most_commented_stream' => $this['top_stats']['most_commented_stream'] ?? null,
                'most_reacted_video' => $this['top_stats']['most_reacted_video'] ?? null,
                'most_commented_video' => $this['top_stats']['most_commented_video'] ?? null,
            ],
        ];
    }
}
