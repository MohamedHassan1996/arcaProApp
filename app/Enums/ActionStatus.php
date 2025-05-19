<?php

namespace App\Enums;

enum ActionStatus: string{

    case NO_ACTIVE = "";

    case CREATE = "CREATE";

    case UPDATE = "UPDATE";

    case DELETE = "DELETE";

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
