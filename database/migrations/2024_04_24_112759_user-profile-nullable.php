<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('user', function (Blueprint $table) {
          $table->string('last_name', 255)->nullable()->change();
          $table->string('first_name', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('user', function (Blueprint $table) {
          $table->string('last_name', 255)->change();
          $table->string('first_name', 255)->change();
        });
    }
};
