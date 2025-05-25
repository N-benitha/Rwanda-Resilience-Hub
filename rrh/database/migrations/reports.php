<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->enum('type', ['flood_risk', 'weather_analysis', 'sensor_summary', 'predictive']);
            $table->text('summary');
            $table->longText('content');
            $table->json('data_sources')->nullable();
            $table->json('charts_config')->nullable();
            $table->timestamp('period_start');
            $table->timestamp('period_end');
            $table->string('status')->default('generated');
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};