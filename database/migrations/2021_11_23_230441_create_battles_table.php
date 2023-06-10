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
            $table->unsignedInteger('thread_id')->index();
            $table->unsignedInteger('post_id');
            $table->string('created_binggan'); //发起者饼干
            $table->string('challenger_binggan')->nullable(); //挑战者饼干
            $table->tinyInteger('chara_group')->default(0); //大乱斗主题(1=共通  2=咒)
            $table->tinyInteger('progress')->default(0)->index(); //0=等待挑战者；1=挑战者已参加；2=正常结束；3=超时关闭
            $table->integer('initiator_user_id'); // 发起者id
            $table->unsignedTinyInteger('initiator_chara'); //发起者角色id
            $table->boolean('initiator_is_custom_chara')->default(0); //发起者是否自定义角色
            $table->unsignedTinyInteger('initiator_rand_num')->nullable(); //发起者骰子数字
            $table->integer('challenger_user_id')->nullable(); //挑战者id
            $table->unsignedTinyInteger('challenger_chara')->nullable(); //挑战者角色id
            $table->boolean('challenger_is_custom_chara')->default(0); //挑战者是否自定义角色
            $table->unsignedTinyInteger('challenger_rand_num')->nullable(); //挑战者骰子数字
            $table->tinyInteger('result')->default(0); //0=进行中；1=发起者胜利；2=挑战者胜利；3=平局
            $table->unsignedInteger('battle_olo'); //大乱斗金额
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
