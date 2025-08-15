<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSellPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sell_points', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_active')->default(0);
            $table->double('max_amount')->default(5000); // default 1000
            $table->integer('owner_id')->unsigned()->nullable();
            $table->foreign('owner_id')->references('id')->on('owners')->cascadeOnUpdate();
            $table->integer('position_id')->unsigned()->nullable();
            $table->foreign('position_id')->references('id')->on('positions')->cascadeOnUpdate();
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnUpdate();
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
        Schema::dropIfExists('sell_points');
    }
}
