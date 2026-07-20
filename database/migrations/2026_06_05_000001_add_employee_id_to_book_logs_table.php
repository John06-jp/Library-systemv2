<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('book_logs', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('student_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_logs', function (Blueprint $table) {
            if (Schema::hasColumn('book_logs', 'employee_id')) {
                $table->dropColumn('employee_id');
            }
        });
    }
};
