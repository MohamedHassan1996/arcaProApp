<?php

namespace App\Models;

use App\Enums\Maintenance\MaintenanceType;
use Illuminate\Database\Eloquent\Model;

class ProCalendarEvent extends Model
{
    protected $connection = 'proMaintenances';
    protected $table = 'events';
}
