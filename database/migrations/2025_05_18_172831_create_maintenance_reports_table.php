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
        Schema::create('maintenance_reports', function (Blueprint $table) {
            $table->id();
            $table->string('maintenance_guid', 36)->nullable();
            $table->time('leave_at')->nullable();
            $table->time('arrive_at')->nullable();
            $table->boolean('is_one_work_period')->default(1);
            $table->json('work_times')->nullable();
            $table->tinyInteger('numbe_of_meals')->default(0);
            $table->string('product_codices')->nullable();
            $table->text('note')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_reports');
    }
};
