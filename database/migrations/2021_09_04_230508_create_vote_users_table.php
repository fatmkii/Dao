<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoteUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i = 1; $i < 3; $i++) {
            Schema::create('vote_users_' . $i, function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->index();
                $table->integer('vote_question_id')->index();
                $table->json('options_id'); //用json存放数组，方便日后改为多选
                $table->timestamps();
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
        Schema::dropIfExists('vote_users');
    }
}
