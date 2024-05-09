<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('item_supply_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_source_id');
            $table->unsignedBigInteger('item_meta_id');
            $table->enum('on_low_stock_action', ['email', 'notify', 'show', 'hidden']);
            $table->unsignedBigInteger('default_restock_quantity');
            $table->unsignedBigInteger('restock_point');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_supply_data');
    }
};
