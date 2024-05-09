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
        Schema::create('item_price_modifier', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_sale_data_id');
            $table->unsignedDouble('set_price');
            $table->timestamp('started_at');
            $table->timestamp('ended_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_price_modifier');
    }
};
