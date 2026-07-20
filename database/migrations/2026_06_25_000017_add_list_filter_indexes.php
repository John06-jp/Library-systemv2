<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->index('lastname');
            $table->index('course');
            $table->index('year');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->index('lastname');
            $table->index('program');
            $table->index('year_start_work');
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->index('scanned_at');
            $table->index('status');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('lname');
            $table->index('fname');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex(['lastname']);
            $table->dropIndex(['course']);
            $table->dropIndex(['year']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex(['lastname']);
            $table->dropIndex(['program']);
            $table->dropIndex(['year_start_work']);
        });

        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropIndex(['scanned_at']);
            $table->dropIndex(['status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['lname']);
            $table->dropIndex(['fname']);
        });
    }
};
