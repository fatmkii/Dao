<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesIndexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages_index', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->unsignedMediumInteger('user_id')->index();
            $table->unsignedTinyInteger('index_type'); //1=公告类;2=用户间消息
            $table->unsignedMediumInteger('announcement_id')->nullable(); //公告id
            $table->unsignedMediumInteger('user_msg_group_id')->nullable(); //用户之间对话聚合成一个窗口，暂时没做
            $table->text('title');
            $table->string('sub_title')->default('[公告]');
            $table->boolean('is_read')->default(false);
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
        Schema::dropIfExists('messages_index');
    }
}
