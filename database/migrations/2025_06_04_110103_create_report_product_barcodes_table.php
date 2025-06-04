<?php

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
            $table->string('product_barcode');
            $table->string('product_guid', 36);
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
