<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'receiver']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($this->message->message_type === 'direct') {
            // Broadcast to both sender and receiver for direct messages
            $channels[] = new PrivateChannel('user.' . $this->message->sender_id);
            if ($this->message->receiver_id) {
                $channels[] = new PrivateChannel('user.' . $this->message->receiver_id);
            }
        } elseif ($this->message->message_type === 'room') {
            // Broadcast to room channel
            $channels[] = new PresenceChannel('room.' . $this->message->room_id);
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'receiver_id' => $this->message->receiver_id,
            'room_id' => $this->message->room_id,
            'message_type' => $this->message->message_type,
            'message' => $this->message->message,
            'metadata' => $this->message->metadata,
            'is_read' => $this->message->is_read,
            'created_at' => $this->message->created_at,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
            ],
            'receiver' => $this->message->receiver ? [
                'id' => $this->message->receiver->id,
                'name' => $this->message->receiver->name,
            ] : null,
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
