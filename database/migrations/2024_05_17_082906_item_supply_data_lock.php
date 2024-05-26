<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('item_supply_data', function (Blueprint $table) {
      $table->boolean('is_lock')->default(false);
    });
  }

  public function down(): void
  {
    Schema::table('item_supply_data', function (Blueprint $table) {
      $table->dropColumn('is_lock');
    });
  }
};
