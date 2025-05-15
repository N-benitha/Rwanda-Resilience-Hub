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
        Schema::create('flood_predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('location');
            $table->float('risk_percentage');
            $table->json('rainfall_data')->nullable();
            $table->json('river_level_data')->nullable();
            $table->json('soil_moisture_data')->nullable();
            $table->timestamp('prediction_date');
            $table->integer('validity_period')->default(7); // days
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flood_predictions');
    }
};