<?php

namespace App;

enum Roles : String
{
    case Admin = "admin";
    case Approver = "approver";
    case Employee = "employee";
}
