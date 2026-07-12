<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('ticket-list.departments.{departmentId}', function (
    User $user,
    int $departmentId
): bool {
    if ($user->isAdmin()) {
        return false;
    }

    if ($user->department_id === null) {
        return false;
    }

    return (int) $user->department_id === $departmentId;
});