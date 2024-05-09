<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('item_meta', function (Blueprint $table) {
          $table->string('stock_keeping_unit', 255)->nullable()->change();
          $table->string('universal_product_code', 255)->nullable()->change();
          $table->unsignedBigInteger('brand')->nullable()->change();
          $table->unsignedBigInteger('category')->nullable()->change();
          $table->json('others')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('item_meta', function (Blueprint $table) {
          $table->string('stock_keeping_unit', 255)->change();
          $table->string('universal_product_code', 255)->change();
          $table->unsignedBigInteger('brand')->change();
          $table->unsignedBigInteger('category')->change();
          $table->json('others')->change();
        });
    }
};
