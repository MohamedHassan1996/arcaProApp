<?php

namespace App\Models;

use App\Enums\Maintenance\MaintenanceType;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    protected $fillable = [
        'title',
        'description',
        'start_at',
        'end_at',
        'is_all_day',
        'maintenance_type',
        'client_guid',
    ];

    protected function casts()
    {
        return [
            'maintenance_type' => MaintenanceType::class,
        ];
    }
}
