<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceStockItem extends Model
{
    protected $fillable = [
        'maintenance_report_id',
        'stock_item_guid',
        'quantity'
    ];
}
