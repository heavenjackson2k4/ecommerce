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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('order_code', 20)->unique();
            $table->string('customer_name', 255);
            $table->text('shipping_address');
            $table->text('note')->nullable();
            $table->decimal('total_amount', 12, 2);
            $table->decimal('shipping_fee',12,2)->default(0);
            $table->decimal('discount_amount',12,2)->default(0);
            $table->enum('payment_method', ['cod', 'banking', 'momo', 'vnpay'])->default('cod');
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
