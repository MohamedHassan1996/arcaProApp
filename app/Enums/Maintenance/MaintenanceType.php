<?php

namespace App\Enums\Maintenance;

enum MaintenanceType: int{
    case INSTALLATION = 0;
    case MAINTANANCE = 1;
    case CONTROL = 2;
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function labels(): array
    {
        return array_column(self::cases(), 'name');
    }


}
