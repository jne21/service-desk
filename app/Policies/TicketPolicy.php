<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $ticket->department_id !== null
            && $ticket->department_id === $user->department_id;
    }

    public function update(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $ticket->department_id !== null
            && $ticket->department_id === $user->department_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->department_id === null) {
            return false;
        }

        if ($user->isManager()) {
            if ($ticket->department_id !== $user->department_id) {
                return false;
            }

            return ! $ticket->status?->is_final;
        }

        return false;
    }
}
