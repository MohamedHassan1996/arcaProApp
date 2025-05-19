<?php
namespace App\Enums;
enum IsMain:int{
    case MAIN = 1;
    case SECONDARY =0;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

}
