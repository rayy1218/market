<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('checkout', function (Blueprint $table) {
          $table->dropColumn('timestamp');
          $table->dropColumn('status');
        });
    }

    public function down(): void
    {
        Schema::table('checkout', function (Blueprint $table) {
          $table->timestamp('timestamp');
          $table->enum('status', ['completed', 'pending', 'started']);
        });
    }
};
