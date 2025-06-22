<?php

use App\Enums\Maintenance\MaintenanceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('report_product_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_report_id')->constrained();
            $table->tinyInteger('maintenance_type')->default(MaintenanceType::INSTALLATION->value);
            $table->string('product_barcode');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_product_barcodes');
    }
};
