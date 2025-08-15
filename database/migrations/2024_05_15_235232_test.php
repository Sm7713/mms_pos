<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Test extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // $table->integer('order_id')->unsigned()->nullable();
            // $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('payments', function (Blueprint $table) {
        //     // // // default 1000
        //      $table->drop('sell_point_id')->unsigned()->nullable();
        //      $table->foreign('sell_point_id')->references('id')->on('sell_points')->cascadeOnUpdate();
        //  });
    }
}
