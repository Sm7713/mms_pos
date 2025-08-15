<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->increments('id');
            $table->string('u_id')->unique();
            $table->string('username')->unique();
            $table->string('password')->nullable();
            $table->boolean('sell_status')->default(0);
            $table->dateTime('sell_date')->nullable();
            $table->integer('category_id')->unsigned();
            $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->integer('status_id')->unsigned()->default(1);
            $table->foreign('status_id')->references('id')->on('card_statuses')->cascadeOnUpdate();
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
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
        Schema::dropIfExists('cards');
    }
}
