<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_transaction_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('type', ['stock_in', 'stock_out', 'transfer', 'split', 'order_stock_in', 'checkout_stock_out']);
            $table->unsignedBigInteger('stock_in_item_id')->nullable();
            $table->unsignedBigInteger('stock_in_quantity')->nullable();
            $table->unsignedBigInteger('stock_in_location_id')->nullable();
            $table->unsignedBigInteger('stock_out_item_id')->nullable();
            $table->unsignedBigInteger('stock_out_quantity')->nullable();
            $table->unsignedBigInteger('stock_out_location_id')->nullable();
            $table->unsignedBigInteger('checkout_item_id')->nullable();
            $table->unsignedBigInteger('order_item_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_transaction_log');
    }
};
