<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->enum('attendance_mode', ['office', 'wfh', 'business_trip'])
                ->default('office')
                ->after('attendance_status');

            // WFH / business-trip context (nullable — existing rows keep working).
            $table->string('check_in_ip_address', 45)->nullable()->after('attendance_mode');
            $table->string('check_in_user_agent')->nullable()->after('check_in_ip_address');
            $table->string('work_location')->nullable()->after('check_in_user_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['attendance_mode', 'check_in_ip_address', 'check_in_user_agent', 'work_location']);
        });
    }
};
