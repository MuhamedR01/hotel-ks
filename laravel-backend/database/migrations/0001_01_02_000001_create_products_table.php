<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->string('category', 100)->nullable();
            $table->boolean('available')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('featured')->default(false);
            $table->decimal('rating', 3, 1)->nullable();
            $table->integer('reviews')->default(0);
            $table->boolean('has_sizes')->default(false);
            $table->json('sizes')->nullable();

            // Image metadata columns
            $table->string('image_name', 255)->nullable();
            $table->integer('image_size')->nullable();
            $table->string('image_type', 100)->nullable();

            $table->string('image_2_name', 255)->nullable();
            $table->integer('image_2_size')->nullable();
            $table->string('image_2_type', 100)->nullable();

            $table->string('image_3_name', 255)->nullable();
            $table->integer('image_3_size')->nullable();
            $table->string('image_3_type', 100)->nullable();

            $table->string('image_4_name', 255)->nullable();
            $table->integer('image_4_size')->nullable();
            $table->string('image_4_type', 100)->nullable();

            $table->string('image_5_name', 255)->nullable();
            $table->integer('image_5_size')->nullable();
            $table->string('image_5_type', 100)->nullable();

            $table->timestamps();
        });

        // Add LONGBLOB columns for image data (not supported by Blueprint)
        DB::statement('ALTER TABLE products ADD COLUMN image LONGBLOB NULL AFTER sizes');
        DB::statement('ALTER TABLE products ADD COLUMN image_2 LONGBLOB NULL AFTER image_type');
        DB::statement('ALTER TABLE products ADD COLUMN image_3 LONGBLOB NULL AFTER image_2_type');
        DB::statement('ALTER TABLE products ADD COLUMN image_4 LONGBLOB NULL AFTER image_3_type');
        DB::statement('ALTER TABLE products ADD COLUMN image_5 LONGBLOB NULL AFTER image_4_type');
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
