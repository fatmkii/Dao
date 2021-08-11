<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserActivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i = 8; $i < 10; $i++) {
            Schema::create('user_actives_2021_' . $i, function (Blueprint $table) {
                $table->id();
                $table->string('binggan')->index();
                $table->string('user_id')->index();
                $table->string('active');
                $table->integer('post_id')->nullable();
                $table->integer('thread_id')->nullable();
                $table->integer('forum_id')->nullable();
                $table->string('binggan_target')->nullable();
                $table->tinyText('content')->nullable();
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
        Schema::dropIfExists('user_actives');
    }
}
