<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kiểm tra bảng product_variants cũ có tồn tại không
        if (Schema::hasTable('product_variants')) {
            // Lấy dữ liệu từ bảng cũ
            $oldVariants = DB::table('product_variants')->get();

            foreach ($oldVariants as $old) {
                // Tìm product để xác định loại
                $product = DB::table('products')->where('id', $old->product_id)->first();

                if ($product) {
                    if ($product->product_type === 'SHOE') {
                        // Chèn vào shoes_variants
                        DB::table('shoes_variants')->insert([
                            'product_id' => $old->product_id,
                            'size' => $old->size,
                            'color' => $old->color,
                            'stud_type' => $old->stud_type ?? 'FG', // fallback nếu null
                            'price_override' => $old->price_override,
                            'quantity' => $old->stock_quantity,
                            'status' => $old->status,
                            'created_at' => $old->created_at,
                            'updated_at' => $old->updated_at,
                        ]);
                    } elseif ($product->product_type === 'CLOTH') {
                        // Chèn vào clothes_variants
                        DB::table('clothes_variants')->insert([
                            'product_id' => $old->product_id,
                            'size' => $old->size,
                            'color' => $old->color,
                            'price_override' => $old->price_override,
                            'quantity' => $old->stock_quantity,
                            'status' => $old->status,
                            'created_at' => $old->created_at,
                            'updated_at' => $old->updated_at,
                        ]);
                    }
                }
            }

            // Xóa bảng cũ
            Schema::dropIfExists('product_variants');
        }

        // Xóa cột stud_type khỏi bảng shoes (nếu còn)
        if (Schema::hasTable('shoes') && Schema::hasColumn('shoes', 'stud_type')) {
            Schema::table('shoes', function (Blueprint $table) {
                $table->dropColumn('stud_type');
            });
        }
    }

    public function down(): void
    {
        // Không thể rollback dễ dàng, nhưng có thể tạo lại bảng cũ nếu cần
        // Đây là fallback đơn giản
        if (!Schema::hasTable('product_variants')) {
            Schema::create('product_variants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('sku', 100)->unique();
                $table->string('color', 50);
                $table->string('size', 20);
                $table->unsignedInteger('stock_quantity')->default(0);
                $table->decimal('price_override', 12, 2)->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }
    }
};