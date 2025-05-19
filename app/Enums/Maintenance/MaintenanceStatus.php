<?php

namespace App\Enums\Maintenance;

enum MaintenanceStatus: string{
    case PROGRAMMATO = '66cb1c1b-693d-46a8-b1e7-4d925163467e';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
