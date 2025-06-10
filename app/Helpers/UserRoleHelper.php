<?php

namespace App\Helpers;

use App\Models\User;

class UserRoleHelper
{
    public static function isAdmin(User $user): bool
    {
        return $user->role === 'admin';
    }

    public static function isPenjual(User $user): bool
    {
        return $user->role === 'penjual';
    }

    public static function isPembeli(User $user): bool
    {
        return $user->role === 'pembeli';
    }
}
