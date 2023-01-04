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
            $table->unsignedInteger('thread_id');
            $table->unsignedInteger('post_id');
            $table->string('created_binggan');
            $table->string('challenger_binggan')->nullable();
            $table->tinyInteger('chara_group')->default(0);
            $table->tinyInteger('progress')->default(0)->index(); //0=等待挑战者；1=挑战者已参加；2=正常结束；3=超时关闭
            $table->integer('initiator_user_id');
            $table->unsignedTinyInteger('initiator_chara');
            $table->unsignedTinyInteger('initiator_rand_num')->nullable();
            $table->integer('challenger_user_id')->nullable();
            $table->unsignedTinyInteger('challenger_chara')->nullable();
            $table->unsignedTinyInteger('challenger_rand_num')->nullable();
            $table->tinyInteger('result')->default(0); //0=进行中；1=发起者胜利；2=挑战者胜利；3=平局
            $table->unsignedInteger('battle_olo');
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
