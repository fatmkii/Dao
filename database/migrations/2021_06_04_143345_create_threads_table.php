<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThreadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('threads', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->tinyInteger('sub_id')->unsigned()->default(0)->index(); //用来排序的。10=本岛公告、99=全岛公告
            $table->integer('forum_id')->index();
            $table->integer('vote_question_id')->nullable(); //投票贴的id
            $table->integer('gamble_question_id')->nullable(); //菠菜贴的id
            $table->string('nickname');
            $table->text('title');
            $table->string('sub_title')->default('[闲聊]');
            $table->tinyInteger('random_heads_group')->default(1);
            $table->integer('posts_num')->default(0); //回帖数
            $table->string('title_color')->nullable(); //自定义标题颜色
            $table->string('created_IP')->nullable()->default(null);
            $table->string('created_binggan')->nullable()->default(null);
            $table->boolean('anti_jingfen')->default(0);
            $table->timestamp('nissin_date')->nullable();
            $table->tinyInteger('is_deleted')->default(0); //0=正常；1=被用户删除；2=被管理员删除
            $table->boolean('can_battle')->default(1);
            $table->boolean('is_delay')->default(0);
            $table->mediumInteger('locked_by_coin')->unsigned()->default(0);
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
        Schema::dropIfExists('threads');
    }
}
