<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableRevisionProductsPriceAndStockPrice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revision_products', function (Blueprint $table) {
            $table->integer('price')->nullable();
            $table->integer('purchase_price')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revision_products', function (Blueprint $table) {
            $table->dropColumn(['price', 'purchase_price']);
        });
    }
}
