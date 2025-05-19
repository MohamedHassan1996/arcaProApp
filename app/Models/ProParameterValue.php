<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProParameterValue extends Model
{
    protected $connection = 'proMaintenances';

    protected $table = 'parameter_values';

}
