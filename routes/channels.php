<?php

use Illuminate\Support\Facades\Broadcast;
use App\Enums\Roles;

Broadcast::channel('approver.division.{divisionId}', function ($user, $divisionId) {
    return $user
        && $user->role === Roles::Approver->value
        && (int)$user->division_id === (int)$divisionId;
});

Broadcast::channel('manager.approval', function ($user) {
    return $user
        && $user->role === Roles::Manager->value;
});

Broadcast::channel('send-message', function ($user) {
    return true;
});
