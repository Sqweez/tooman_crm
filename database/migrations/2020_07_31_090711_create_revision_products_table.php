<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revision_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('revision_id');
            $table->integer('product_id');
            $table->integer('stock_quantity');
            $table->integer('fact_quantity');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('revision_products');
    }
}
