<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmojiContestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emoji_contest', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('emoji_id')->unique();
            $table->unsignedTinyInteger('emoji_group_id')->index();
            $table->unsignedInteger('votes_num_total');
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
        Schema::dropIfExists('emoji_contest');
    }
}
