<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast; // Importante ni
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// I-implement ang ShouldBroadcast aron ma-broadcast ni sa Laravel
class LyricsUpdated implements ShouldBroadcast
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
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('lyrics-channel');
    }

    /**
     * Optional: I-customize ang broadcast name
     */
    public function broadcastAs(): string
    {
        return 'LyricsUpdated';
    }
}