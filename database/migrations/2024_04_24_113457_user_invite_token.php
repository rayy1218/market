<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
          $table->string('password', 255)->nullable()->change();
          $table->string('invite_token', '255')->nullable();
          $table->enum('status', ['pending', 'active', 'inactive']);
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
          $table->string('password', 255)->change();
          $table->dropColumn('invite_token');
          $table->dropColumn('status');
        });
    }
};
