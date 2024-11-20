<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveOvertimeAndWorkOntimeFromAttendances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // 不要なカラムを削除
            $table->dropColumn(['overtime', 'work_ontime']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances', function (Blueprint $table) {
            // downメソッドでは削除したカラムを戻す処理を記述
            $table->unsignedSmallInteger('overtime')->default(0);
            $table->string('work_ontime')->default('通常');
        });
    }
}
