<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropAttendanceIdFromBreakTimeCorrections extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('break_time_corrections', function (Blueprint $table) {

            $table->dropForeign('break_time_corrections_attendance_id_foreign');

            $table->dropColumn('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('break_time_corrections', function (Blueprint $table) {

            $table->unsignedBigInteger('attendance_id')->nullable();

            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
        });
    }
}
