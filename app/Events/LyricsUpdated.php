<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Use NOW for instant broadcast
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LyricsUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Broadcast channel
     */
    public function broadcastOn(): Channel
    {
        return new Channel('lyrics-channel');
    }

    /**
     * Custom event name (frontend will listen to this)
     */
    public function broadcastAs(): string
    {
        return 'lyrics.updated';
    }

    /**
     * Control the exact data sent to frontend
     */
    public function broadcastWith(): array
    {
        return [
            'text' => $this->data['text'] ?? '',
            'fontSize' => $this->data['fontSize'] ?? 60,
            'background' => $this->data['background'] ?? 'none',
            'updatedAt' => $this->data['updatedAt'] ?? null,
        ];
    }
}