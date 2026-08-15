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
        Schema::create('shoe_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shoe_id')->constrained('shoes')->onDelete('cascade');
            $table->string('image_url', 500);
            $table->string('public_id', 255);
            $table->string('color', 50);
            $table->string('stud_type', 20)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();

            $table->index('shoe_id');
            $table->index('color');
            $table->index('is_primary');

            $table->unique(['shoe_id', 'color', 'is_primary'],'unique_primary_per_color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shoe_images');
    }
};
