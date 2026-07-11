<?php

namespace App\Services\Contracts;

use App\Models\Ticket;
use App\Models\TicketSource;
use App\Models\User;

interface TicketChangeLoggerInterface
{
    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';
    public const EVENT_RESTORED = 'restored';

    public function logUserAction(
        Ticket $ticket,
        User $user,
        string $event,
        ?array $changes = null
    ): void;

    public function logSourceAction(
        Ticket $ticket,
        TicketSource $source,
        string $event,
        ?array $changes = null
    ): void;

    public function buildChanges(Ticket $ticket, array $newValues): array;
}
