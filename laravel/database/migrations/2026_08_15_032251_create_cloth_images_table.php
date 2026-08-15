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
        Schema::create('cloth_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cloth_id')->constrained('clothes')->onDelete('cascade');
            $table->string('image_url', 500);
            $table->string('public_id', 255);
            $table->string('color',50);
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('cloth_id');
            $table->index('color');
            $table->index('is_primary');
            $table->unique(['cloth_id', 'color', 'is_primary'], 'unique_primary_per_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cloth_images');
    }
};
