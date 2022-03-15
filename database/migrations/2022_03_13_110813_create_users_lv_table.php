<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersLvTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_lv', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unique();
            $table->integer('title_pingbici')->default(0);
            $table->integer('content_pingbici')->default(0);
            $table->integer('fjf_pingbici')->default(0);
            $table->integer('my_emoji')->default(0);
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
        Schema::dropIfExists('users_lv');
    }
}
