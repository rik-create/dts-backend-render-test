<?php

namespace App\Enums;

use App\Models\UserGroup;

enum UserGroupEnum: int
{
    case OFFICE_ADMINS = 1;
    case ACCOUNTING_STAFF = 2;
    case RECORDS_STAFF = 3;


    public static function getIdByName(string $name): int
    {
        // Hanapin sa DB base sa name sa image_33a0fe.png
        $databaseId = UserGroup::where('name', $name)->value('id');
        
        if ($databaseId) return $databaseId;

        // Fallback matching
        return match ($name) {
            'Office Admins'    => self::OFFICE_ADMINS->value,
            'Accounting Staff' => self::ACCOUNTING_STAFF->value,
            'Records Staff'    => self::RECORDS_STAFF->value,
            default            => self::RECORDS_STAFF->value,
        };
    }
}
