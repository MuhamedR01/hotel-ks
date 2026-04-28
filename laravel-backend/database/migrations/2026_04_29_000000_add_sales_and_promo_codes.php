<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ---- Sale on products ----
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'sale_percent')) {
                // 0–99 (%) — null/0 means no sale
                $table->decimal('sale_percent', 5, 2)->nullable()->after('price');
            }
        });

        // ---- Promo / discount codes ----
        if (!Schema::hasTable('promo_codes')) {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                // percent | fixed | free_shipping
                $table->string('discount_type', 20)->default('percent');
                $table->decimal('discount_value', 8, 2)->default(0);
                $table->decimal('min_subtotal', 10, 2)->nullable();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('expires_at')->nullable(); // null = forever
                $table->unsignedInteger('max_uses')->nullable(); // null = unlimited
                $table->unsignedInteger('times_used')->default(0);
                $table->boolean('is_active')->default(true);
                $table->string('description', 255)->nullable();
                $table->timestamps();
                $table->index('is_active');
            });
        }

        // ---- Promo info on orders ----
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'promo_code')) {
                $table->string('promo_code', 50)->nullable()->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('promo_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'sale_percent')) {
                $table->dropColumn('sale_percent');
            }
        });
        Schema::table('orders', function (Blueprint $table) {
            foreach (['promo_code', 'discount_amount'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
        Schema::dropIfExists('promo_codes');
    }
};
