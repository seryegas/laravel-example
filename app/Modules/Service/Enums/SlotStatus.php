<?php

declare(strict_types=1);

namespace App\Modules\Service\Enums;

enum SlotStatus: string
{
    case Available = 'available';
    case Booked = 'booked';
    case Blocked = 'blocked';
}
