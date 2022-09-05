<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableRevisions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            $table->unsignedInteger('checking_user_id')->nullable();
            $table->string('original_loaded_revision_file')->nullable();
            $table->string('original_pivot_file')->nullable();
            $table->string('final_pivot_file')->nullable();
            $table->unsignedInteger('status')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revisions', function (Blueprint $table) {
            //
        });
    }
}
