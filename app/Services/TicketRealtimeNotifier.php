<?php

namespace App\Services;

use App\Events\TicketChanged;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;

class TicketRealtimeNotifier
{
    public const TYPE_CREATED = 'created';
    public const TYPE_UPDATED = 'updated';
    public const TYPE_RESTORED = 'restored';
    public const TYPE_DELETED = 'deleted';
    public const TYPE_REMOVED_FROM_ACCESS = 'removed_from_access';

    public function created(Ticket $ticket): void
    {
        $this->broadcastToDepartment(
            departmentId: $ticket->department_id,
            type: self::TYPE_CREATED,
            ticket: $ticket,
        );
    }

    public function updated(Ticket $ticket, ?int $oldDepartmentId = null): void
    {
        $currentDepartmentId = $ticket->department_id;

        if (
            $oldDepartmentId !== null
            && $oldDepartmentId !== $currentDepartmentId
        ) {
            $this->broadcastRemovedFromAccess(
                ticketId: $ticket->id,
                departmentId: $oldDepartmentId,
            );

            $this->broadcastToDepartment(
                departmentId: $currentDepartmentId,
                type: self::TYPE_UPDATED,
                ticket: $ticket,
            );

            return;
        }

        $this->broadcastToDepartment(
            departmentId: $currentDepartmentId,
            type: self::TYPE_UPDATED,
            ticket: $ticket,
        );
    }

    public function restored(Ticket $ticket): void
    {
        $this->broadcastToDepartment(
            departmentId: $ticket->department_id,
            type: self::TYPE_RESTORED,
            ticket: $ticket,
        );
    }

    public function deleted(Ticket $ticket): void
    {
        $this->broadcastToDepartment(
            departmentId: $ticket->department_id,
            type: self::TYPE_DELETED,
            ticket: $ticket,
        );
    }

    private function broadcastToDepartment(
        ?int $departmentId,
        string $type,
        Ticket $ticket
    ): void {
        if ($departmentId === null) {
            return;
        }

        $ticket->loadMissing([
            'status',
            'department',
            'user',
            'source',
        ]);

        TicketChanged::dispatch(
            type: $type,
            ticketId: $ticket->id,
            ticket: TicketResource::make($ticket)->resolve(),
            channelNames: [
                $this->departmentChannel($departmentId),
            ],
        );
    }

    private function broadcastRemovedFromAccess(
        int $ticketId,
        ?int $departmentId
    ): void {
        if ($departmentId === null) {
            return;
        }

        TicketChanged::dispatch(
            type: self::TYPE_REMOVED_FROM_ACCESS,
            ticketId: $ticketId,
            ticket: null,
            channelNames: [
                $this->departmentChannel($departmentId),
            ],
        );
    }

    private function departmentChannel(int $departmentId): string
    {
        return "ticket-list.departments.{$departmentId}";
    }
}