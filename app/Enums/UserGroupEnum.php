<?php

namespace App\Enums;

enum UserGroupEnum: int
{
    case OFFICE_ADMINS = 1;
    case ACCOUNTING_STAFF = 2;
    case RECORDS_STAFF = 3;
}
