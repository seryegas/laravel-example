<?php

declare(strict_types=1);

namespace App\Modules\Core\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Manager = 'manager';
    case Client = 'client';
}
