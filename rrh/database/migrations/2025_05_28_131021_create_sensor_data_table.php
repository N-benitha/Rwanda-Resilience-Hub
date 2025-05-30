<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('sensor_id')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('location_name')->nullable();
            $table->decimal('rainfall', 8, 2)->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->decimal('humidity', 5, 2)->nullable();
            $table->decimal('wind_speed', 5, 2)->nullable();
            $table->decimal('water_level', 8, 2)->nullable();
            $table->decimal('flow_level', 8, 2)->nullable();
            $table->decimal('soil_moisture', 5, 2)->nullable();
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->json('additional_data')->nullable();
            $table->boolean('is_validated')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_data');
    }
};