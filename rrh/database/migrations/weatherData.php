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
        Schema::create('weather_data', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_name')->nullable();
            $table->decimal('temperature', 5, 2);
            $table->decimal('humidity', 5, 2);
            $table->decimal('precipitation', 8, 2)->default(0);
            $table->decimal('wind_speed', 5, 2);
            $table->decimal('pressure', 7, 2);
            $table->string('weather_condition');
            $table->text('description')->nullable();
            $table->json('raw_data')->nullable();
            $table->string('data_source'); // openweathermap, nasa_power
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index('recorded_at');
            $table->index('data_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weather_data');
    }
};