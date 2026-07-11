<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketChange;
use App\Models\TicketSource;
use App\Models\User;
use App\Services\Contracts\TicketChangeLoggerInterface;

class TicketChangeLogger implements TicketChangeLoggerInterface
{
    /**
     * Поля заявки, зміни яких ми пишемо в історію.
     */
    private const TRACKED_FIELDS = [
        'title',
        'description',
        'status_id',
        'department_id',
    ];

    public function logUserAction(
        Ticket $ticket,
        User $user,
        string $event,
        ?array $changes = null
    ): void {
        $this->log(
            ticket: $ticket,
            event: $event,
            changes: $changes,
            user: $user,
            source: null,
        );
    }

    public function logSourceAction(
        Ticket $ticket,
        TicketSource $source,
        string $event,
        ?array $changes = null
    ): void {
        $this->log(
            ticket: $ticket,
            event: $event,
            changes: $changes,
            user: null,
            source: $source,
        );
    }

    public function buildChanges(Ticket $ticket, array $newValues): array
    {
        $changes = [];

        foreach (self::TRACKED_FIELDS as $field) {
            if (! array_key_exists($field, $newValues)) {
                continue;
            }

            $oldValue = $ticket->getAttribute($field);
            $newValue = $newValues[$field];

            if ($oldValue === $newValue) {
                continue;
            }

            $changes[$field] = [
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        return $changes;
    }

    private function log(
        Ticket $ticket,
        string $event,
        ?array $changes,
        ?User $user,
        ?TicketSource $source
    ): void {
        TicketChange::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user?->id,
            'ticket_source_id' => $source?->id,
            'event' => $event,
            'changes' => $changes,
        ]);
    }
}