<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            if (!Schema::hasColumn('meters', 'serial_number'))
                $table->string('serial_number')->nullable()->after('endpoint_id');
            if (!Schema::hasColumn('meters', 'manufacturer'))
                $table->string('manufacturer')->nullable()->after('serial_number');
            if (!Schema::hasColumn('meters', 'notes'))
                $table->text('notes')->nullable()->after('last_maintenance_date');
        });
    }

    public function down(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            foreach (['serial_number','manufacturer','notes'] as $col) {
                if (Schema::hasColumn('meters', $col)) $table->dropColumn($col);
            }
        });
    }
};
