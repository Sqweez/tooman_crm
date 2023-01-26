<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableWorkingDaysColumnCashlessPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('working_days', function (Blueprint $table) {
            $table->unsignedInteger('cashless_payment')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('working_days', function (Blueprint $table) {
            $table->dropColumn('cashless_payment');
        });
    }
}
