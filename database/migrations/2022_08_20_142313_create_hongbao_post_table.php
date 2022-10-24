<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHongbaoPostTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hongbao_post', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->foreignId('thread_id')->constrained();
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('floor')->nullable();
            $table->unsignedSmallInteger('num_total');
            $table->unsignedInteger('olo_total');
            $table->unsignedSmallInteger('num_remains');
            $table->unsignedInteger('olo_remains');
            $table->unsignedTinyInteger('type')->default(1); //1=随机红包, 2=定额红包
            $table->unsignedTinyInteger('key_word_type')->default(1); //1=普通口令红包, 2=抢答红包(答案不显示、回复显示), 3=暗号红包(答案回复都不显示)
            $table->boolean('olo_hide')->default(0); //是否隐藏奥利奥
            $table->string('key_word'); //红包口令
            $table->string('question')->nullable(); //红包问题
            $table->string('message')->nullable(); //回复留言
            $table->softDeletes();
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
        Schema::dropIfExists('hongbao_post');
    }
}
