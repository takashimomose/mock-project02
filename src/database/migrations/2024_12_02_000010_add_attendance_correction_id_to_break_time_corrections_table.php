<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAttendanceCorrectionIdToBreakTimeCorrectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('break_time_corrections', function (Blueprint $table) {
            $table->unsignedBigInteger('attendance_correction_id')->nullable()->after('attendance_id');
            $table->foreign('attendance_correction_id')
                ->references('id')
                ->on('attendance_corrections')
                ->onDelete('cascade'); 
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
            $table->dropForeign(['attendance_correction_id']);
            $table->dropColumn('attendance_correction_id');
        });
    }
};

