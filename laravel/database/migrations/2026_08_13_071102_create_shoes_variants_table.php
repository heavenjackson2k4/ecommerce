<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shoes_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('size', 20);
            $table->string('color', 50);
            $table->string('stud_type', 20);
            $table->decimal('price_override', 12, 2)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['product_id', 'size', 'color', 'stud_type'], 'unique_shoe_variant');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shoes_variants');
    }
};