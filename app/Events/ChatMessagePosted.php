<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessagePosted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Broadcasts to every table taking part in the conversation. A room message
     * carries just the one table; a DM carries both. Each client subscribes only
     * to its own table channel, so a table sees a conversation only when it is a
     * participant.
     *
     * @param  array<int, string>  $tableIds
     * @param  array<string, mixed>  $message
     */
    public function __construct(public array $tableIds, public array $message)
    {
    }

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        // Public per-table channels — QR guests are not authenticated users, so
        // channels are scoped by table id only.
        return array_map(
            fn (string $tableId): Channel => new Channel('chat.table.'.$tableId),
            array_values(array_unique($this->tableIds)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return ['message' => $this->message];
    }
}
