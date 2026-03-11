<?php

namespace App\Enums;

use App\Models\UserRole;

enum UserRoleEnum: int
{
    case ADMIN = 1;
    case STAFF = 2;


    public static function getIdByName(string $name): int
    {
        $databaseId = UserRole::where('name', $name)->value('id');
        if ($databaseId) return $databaseId;

        // Fallback based on specific names
        return match ($name) {
            'Administrator' => self::ADMIN->value,
            'Staff'         => self::STAFF->value,
            default         => self::STAFF->value,
        };
    }
}
