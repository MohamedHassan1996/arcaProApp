<?php

namespace App\Models;

use App\Enums\Maintenance\MaintenanceStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MaintenanceDetail extends Model
{
    protected $connection = 'proMaintenances';


}
