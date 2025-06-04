<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportProductBarcode extends Model
{
    protected $fillable = [
        'report_id',
        'product_barcode',
        'product_guid',
    ];
}
