<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shift_record', function (Blueprint $table) {
          $table->dropColumn('timestamp');
          $table->enum('shift_record_type', ['start_shift', 'end_shift', 'start_break', 'end_break'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('shift_record', function (Blueprint $table) {
          $table->timestamp('timestamp');
          $table->enum('shift_record_type', [1, 2, 3, 4])->change();
        });
    }
};
