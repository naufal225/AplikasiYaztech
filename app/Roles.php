<?php

namespace App;

use Spatie\Permission\Contracts\Role;

enum Roles : String
{
    case Admin = "admin";
    case Approver = "approver"; // ini team lead
    case Employee = "employee";
    case Manager = "manager";
    case Finance = "finance";

    public static function values() {
        return array_map(fn($role) => $role->value, self::cases());
    }
}
