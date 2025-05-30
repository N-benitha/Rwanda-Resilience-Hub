<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('flood_risks', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_name')->nullable();
            $table->decimal('risk_percentage', 5, 2);
            $table->enum('risk_level', ['low', 'moderate', 'high', 'critical']);
            $table->decimal('predicted_precipitation', 8, 2);
            $table->decimal('soil_moisture_level', 5, 2)->nullable();
            $table->decimal('river_level', 8, 2)->nullable();
            $table->json('contributing_factors')->nullable();
            $table->json('ai_analysis')->nullable();
            $table->timestamp('prediction_date');
            $table->timestamp('valid_until');
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index('risk_level');
            $table->index('prediction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flood_risks');
    }
};