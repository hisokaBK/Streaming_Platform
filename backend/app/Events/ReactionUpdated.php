<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReactionUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $streamId,
        public array $reactionsSummary,
        public int $reactionsCount
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('stream.' . $this->streamId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reaction.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'reactions_summary' => $this->reactionsSummary,
            'reactions_count' => $this->reactionsCount,
        ];
    }
}
