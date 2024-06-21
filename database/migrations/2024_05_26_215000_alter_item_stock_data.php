<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('item_stock_data', function (Blueprint $table) {
          $table->bigInteger('quantity')->change();
        });
    }

    public function down(): void
    {
        Schema::table('item_stock_data', function (Blueprint $table) {
          $table->unsignedBigInteger('quantity')->change();
        });
    }
};
