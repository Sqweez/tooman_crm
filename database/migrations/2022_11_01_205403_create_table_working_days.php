<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableWorkingDays extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('working_days', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('store_id');
            $table->unsignedInteger('opening_cash_in_hand')->nullable();
            $table->unsignedInteger('closing_cash_in_hand')->nullable();
            $table->unsignedInteger('kaspi_terminal_cash')->nullable();
            $table->unsignedInteger('kaspi_transfers_cash')->nullable();
            $table->unsignedInteger('jysan_transfers_cash')->nullable();
            $table->unsignedInteger('is_enabled')->default(true);
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('working_days');
    }
}
