<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('battles', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->integer('post_id')->index();
            $table->integer('initiator_user_id');
            $table->integer('initiator_head');
            $table->integer('challenger_user_id')->nullable();
            $table->integer('challenger_head')->nullable();
            $table->integer('bet_olo');
            $table->tinyInteger('result')->nullable();
            $table->timestamp('end_date');
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
        Schema::dropIfExists('battles');
    }
}
