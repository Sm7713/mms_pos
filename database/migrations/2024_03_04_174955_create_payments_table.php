<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date');
            $table->double('amount');
            $table->string('notice')->nullable();
            $table->boolean('confirm')->default(0);
            $table->string('method');
            $table->string('transfer_no');
            $table->integer('sell_point_id')->unsigned();
            $table->foreign('sell_point_id')->references('id')->on('sell_points');
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
        Schema::dropIfExists('payments');
    }
}
