<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnBillsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_bills', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date');
            $table->double('total_amount');
            $table->boolean('confirm')->default(0);
            $table->integer('sell_point_id')->unsigned();
            $table->foreign('sell_point_id')->references('id')->on('sell_points')->cascadeOnUpdate();
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
        Schema::dropIfExists('return_bills');
    }
}
