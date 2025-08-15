<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReturnPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('date');
            $table->double('amount');
            $table->string('method');
            $table->integer('return_bill_id')->unsigned();
            $table->foreign('return_bill_id')->references('id')->on('return_bills')->cascadeOnUpdate();
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
        Schema::dropIfExists('return_payments');
    }
}
