<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendMessageEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public int $user_id;
    public int $sender_user_id;
    public string $message;
    public bool $from_inquiry = false;
    /**
     * Create a new event instance.
     */
    public function __construct(int $user_id, int $sender_user_id, string $message, bool $from_inquiry = false)
    {
        $this->user_id = $user_id;
        $this->sender_user_id = $sender_user_id;
        $this->message = $message;
        $this->from_inquiry = $from_inquiry;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('send-message.'.$this->user_id),
        ];
    }
    
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user_id,
            'sender_user_id' => $this->sender_user_id,
            'message' => $this->message,
            'from_inquiry' => $this->from_inquiry
        ];
    }
}
