<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Optional label for the variant selector shown on the product page
            // and in the cart. When NULL, frontend defaults to "Madhësia".
            // Example values: "Madhësia", "Marka", "Ngjyra".
            $table->string('variant_label', 50)->nullable()->after('sizes');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('variant_label');
        });
    }
};
