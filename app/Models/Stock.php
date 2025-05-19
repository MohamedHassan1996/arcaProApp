<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    protected $connection = 'arca_pro';

    protected $table = 'tb_magazzino';


}

