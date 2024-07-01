<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('item_meta', function (Blueprint $table) {
          $table->unsignedBigInteger('default_receive_location')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('item_meta', function (Blueprint $table) {
          $table->dropColumn('default_receive_location');
        });
    }
};
