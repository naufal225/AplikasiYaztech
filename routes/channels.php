<?php

use Illuminate\Support\Facades\Broadcast;
use App\Roles;

Broadcast::channel('approver.division.{divisionId}', function ($user, $divisionId) {
    return $user
        && $user->role === Roles::Approver->value
        && (int)$user->division_id === (int)$divisionId;
});

Broadcast::channel('manager.division.{divisionId}', function ($user, $divisionId) {
    return $user
        && $user->role === Roles::Manager->value
        && (int)$user->division_id === (int)$divisionId;
});

Broadcast::channel('send-message', function ($user) {
    return true;
});
