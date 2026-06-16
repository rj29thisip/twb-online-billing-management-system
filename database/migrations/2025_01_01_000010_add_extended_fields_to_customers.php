<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Personal details
            if (!Schema::hasColumn('customers', 'given_name'))
                $table->string('given_name')->nullable()->after('name');
            if (!Schema::hasColumn('customers', 'family_name'))
                $table->string('family_name')->nullable()->after('given_name');
            if (!Schema::hasColumn('customers', 'date_of_birth'))
                $table->date('date_of_birth')->nullable()->after('family_name');
            if (!Schema::hasColumn('customers', 'gender'))
                $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable()->after('date_of_birth');

            // Address split
            if (!Schema::hasColumn('customers', 'address_line'))
                $table->string('address_line')->nullable()->after('address');
            if (!Schema::hasColumn('customers', 'suburb'))
                $table->string('suburb')->nullable()->after('address_line');
            if (!Schema::hasColumn('customers', 'island'))
                $table->string('island')->nullable()->after('suburb');
            if (!Schema::hasColumn('customers', 'island_code'))
                $table->string('island_code', 10)->nullable()->after('island');

            // Property details
            if (!Schema::hasColumn('customers', 'deed_number'))
                $table->string('deed_number')->nullable()->after('block_number');
            if (!Schema::hasColumn('customers', 'surveyed_date'))
                $table->date('surveyed_date')->nullable()->after('deed_number');
            if (!Schema::hasColumn('customers', 'property_notes'))
                $table->text('property_notes')->nullable()->after('surveyed_date');

            // Record tracking
            if (!Schema::hasColumn('customers', 'created_by'))
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('district_id');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $cols = ['given_name','family_name','date_of_birth','gender',
                     'address_line','suburb','island','island_code',
                     'deed_number','surveyed_date','property_notes','created_by'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    if ($col === 'created_by') {
                        $table->dropConstrainedForeignId('created_by');
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });
    }
};
