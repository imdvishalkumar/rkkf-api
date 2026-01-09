<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop old column if exists (cleanup from previous attempt)
            if (Schema::hasColumn('products', 'category_id')) {
                // Drop FK first if needed, but safe to just drop column in MySQL usually if constraint naming is standard?
                // Safest to drop FK first if we knew the name.
                // Try dropping column, might fail if FK exists?
                // Let's try to drop FK if we can guess name 'products_category_id_foreign'.
                try {
                    $table->dropForeign(['category_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('category_id');
            }

            $table->unsignedBigInteger('product_category_id')->nullable()->after('product_id');
            $table->foreign('product_category_id')->references('id')->on('product_categories')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['product_category_id']);
            $table->dropColumn('product_category_id');
            // Assuming we don't restore category_id blindly
        });
    }
};
