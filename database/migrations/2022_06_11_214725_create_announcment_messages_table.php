<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncmentMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('announcment_messages', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->unsignedMediumInteger('sender_id')->index();
            $table->tinyInteger('type')->default(1); //1=全体公告，其他未定；
            $table->string('title');
            $table->string('sub_title')->default('[公告]');
            $table->mediumText('content')->nullable();
            $table->unsignedMediumInteger('read_num')->default(0); //公告已读数量
            $table->boolean('to_new_users')->default(false);
            $table->softDeletes();
            $table->timestamp('created_at')->nullable(); //在阿里云rds上，不知道为什么not null的话一定会default current_timestamp
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('announcment_messages');
    }
}
