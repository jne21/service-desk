<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }

    public function update(User $user, User $target): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return false;
    }
}
