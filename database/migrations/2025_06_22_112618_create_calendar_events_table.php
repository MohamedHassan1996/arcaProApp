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
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at')->nullable();
            $table->boolean('is_all_day')->default(0);
            $table->tinyInteger('maintenance_type')->default(MaintenanceType::INSTALLATION->value);
            $table->string('location')->nullable();
            $table->string('product_barcode');
            $table->string('address')->nullable();
            $table->string('location')->nullable();
            $table->tinyInteger('is_done', )->default(0);
            $table->string('client_guid', 36)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
