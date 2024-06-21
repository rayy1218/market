<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('checkout', function (Blueprint $table) {
          $table->string('reference_code', 36);
        });
    }

    public function down(): void
    {
        Schema::table('checkout', function (Blueprint $table) {
          $table->dropColumn('reference_code');
        });
    }
};
