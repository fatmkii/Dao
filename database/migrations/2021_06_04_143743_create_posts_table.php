<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i = 1; $i < 3; $i++) {
            Schema::create('posts_' . $i, function (Blueprint $table) {
                $table->id()->startingValue(1000001);
                $table->timestamps();
                $table->tinyInteger('is_deleted')->default(0); //0=正常；1=被用户删除；2=被管理员删除
                $table->integer('forum_id')->index();
                $table->integer('thread_id')->index();
                $table->integer('battle_id')->nullable(); //大乱斗的ID
                $table->integer('floor')->index();
                $table->tinyInteger('random_head')->nullable(); //论坛随机头像功能用
                $table->tinyInteger('created_by_admin')->default(0); //0=一般用户 1=管理员发布，2=系统发布
                $table->mediumText('content')->nullable();
                $table->string('nickname')->nullable();
                $table->boolean('is_anonymous')->default('0');
                $table->string('created_IP')->nullable()->default(null);
                $table->string('created_binggan')->nullable()->default(null);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
