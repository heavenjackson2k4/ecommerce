<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shoe_images', function (Blueprint $table) {
            $table->dropUnique('unique_primary_per_color');
            $table->index(['shoe_id', 'color'], 'shoe_images_color_lookup_index');
        });

        Schema::table('cloth_images', function (Blueprint $table) {
            $table->dropUnique('unique_primary_per_color');
            $table->index(['cloth_id', 'color'], 'cloth_images_color_lookup_index');
        });
    }

    public function down(): void
    {
        // Refuse rollback rather than remove images that the old key cannot accommodate.
        foreach (['shoe_images' => 'shoe_id', 'cloth_images' => 'cloth_id'] as $table => $foreignKey) {
            if (DB::table($table)->groupBy($foreignKey, 'color', 'is_primary')->havingRaw('COUNT(*) > 1')->exists()) {
                throw new RuntimeException('Không thể khôi phục key cũ vì có nhiều ảnh cùng màu và trạng thái ảnh chính. Dữ liệu ảnh được giữ nguyên.');
            }
        }

        Schema::table('shoe_images', function (Blueprint $table) {
            $table->dropIndex('shoe_images_color_lookup_index');
            $table->unique(['shoe_id', 'color', 'is_primary'], 'unique_primary_per_color');
        });

        Schema::table('cloth_images', function (Blueprint $table) {
            $table->dropIndex('cloth_images_color_lookup_index');
            $table->unique(['cloth_id', 'color', 'is_primary'], 'unique_primary_per_color');
        });
    }
};
