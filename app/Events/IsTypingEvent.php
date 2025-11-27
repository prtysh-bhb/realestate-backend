<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IsTypingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public int $user_id;
    public int $sender_user_id;
    /**
     * Create a new event instance.
     */
    public function __construct(int $user_id, int $sender_user_id)
    {
        $this->user_id = $user_id;
        $this->sender_user_id = $sender_user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('is-typing.'.$this->user_id),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'is-typing';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user_id,
            'sender_user_id' => $this->sender_user_id,
        ];
    }
}
