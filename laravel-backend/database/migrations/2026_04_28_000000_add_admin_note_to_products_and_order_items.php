<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Internal note for admins/packers (e.g. color identifier when several
        // products share the same name). Never exposed via the public API.
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'admin_note')) {
                $table->string('admin_note', 255)->nullable()->after('variant_label');
            }
        });

        // Snapshot the admin_note onto each order item so it is preserved even
        // if the product is later edited or deleted.
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'admin_note')) {
                $table->string('admin_note', 255)->nullable()->after('size');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'admin_note')) {
                $table->dropColumn('admin_note');
            }
        });
    }
};
