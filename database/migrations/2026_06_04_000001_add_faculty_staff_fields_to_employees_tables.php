<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['employees', 'pending_employees'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'middle_initial')) {
                    $table->string('middle_initial')->nullable()->after('lastname');
                }
                if (! Schema::hasColumn($tableName, 'designation')) {
                    $table->string('designation')->nullable()->after('position');
                }
                if (! Schema::hasColumn($tableName, 'program')) {
                    $table->string('program', 64)->nullable()->after('designation');
                }
                if (! Schema::hasColumn($tableName, 'year_start_work')) {
                    $table->string('year_start_work', 16)->nullable()->after('program');
                }
                if (! Schema::hasColumn($tableName, 'mobile_number')) {
                    $table->string('mobile_number', 32)->nullable()->after('birth_date');
                }
                if (! Schema::hasColumn($tableName, 'emergency_address')) {
                    $table->text('emergency_address')->nullable()->after('emergency_contact_number');
                }
            });
        }

        Schema::table('employees', function (Blueprint $table) {
            $table->string('department')->nullable()->change();
        });

        Schema::table('pending_employees', function (Blueprint $table) {
            $table->string('department')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (['employees', 'pending_employees'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['designation', 'program', 'year_start_work', 'mobile_number', 'emergency_address'] as $col) {
                    if (Schema::hasColumn($tableName, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
