<?php

namespace App\Models;

use App\Enums\Maintenance\MaintenanceType;
use Illuminate\Database\Eloquent\Model;

class ReportProductBarcode extends Model
{
    protected $fillable = [
        'maintenance_report_id',
        'product_barcode',
        'maintenance_type',
    ];

    protected function casts() {
        return [
            'maintenance_type' => MaintenanceType::class,
        ];
    }
}
