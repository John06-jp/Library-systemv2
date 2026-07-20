<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('email')->nullable()->after('mobile_number');
        });

        Schema::table('pending_students', function (Blueprint $table) {
            $table->string('email')->nullable()->after('mobile_number');
        });

        Schema::table('student_edit_requests', function (Blueprint $table) {
            $table->string('email')->nullable()->after('mobile_number');
        });

        Schema::table('book_reservations', function (Blueprint $table) {
            $table->timestamp('ready_notified_at')->nullable()->after('ready_at');
        });
    }

    public function down(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            $table->dropColumn('ready_notified_at');
        });

        Schema::table('student_edit_requests', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('pending_students', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
