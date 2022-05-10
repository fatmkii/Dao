<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersUploadTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_upload', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('user_id')->index();
            $table->unsignedSmallInteger('forum_id');
            $table->unsignedMediumInteger('thread_id');
            $table->string('img_md5', 32);
            $table->unsignedMediumInteger('img_size');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_upload');
    }
}
