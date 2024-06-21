<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('item_sale_data', function (Blueprint $table) {
          $table->unsignedBigInteger('default_stock_out_location_id');
        });
    }

    public function down(): void
    {
        Schema::table('item_sale_data', function (Blueprint $table) {
          $table->dropColumn('default_stock_out_location_id');
        });
    }
};
