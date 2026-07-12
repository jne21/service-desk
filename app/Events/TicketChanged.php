<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

class TicketChanged implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;

    public function __construct(
        public string $type,
        public int $ticketId,
        public ?array $ticket,
        private array $channelNames
    ) {
    }

    public function broadcastOn(): array
    {
        return array_map(
            fn (string $channelName) => new PrivateChannel($channelName),
            $this->channelNames
        );
    }

    public function broadcastAs(): string
    {
        return 'ticket.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->type,
            'ticket_id' => $this->ticketId,
            'ticket' => $this->ticket,
        ];
    }
}