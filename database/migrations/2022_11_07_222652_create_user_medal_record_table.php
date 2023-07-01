<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMedalRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_medal_record', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedInteger('battle_olo_in')->default(0);
            $table->unsignedInteger('battle_olo_out')->default(0);
            $table->unsignedInteger('hongbao_olo_in')->default(0);
            $table->unsignedInteger('hongbao_olo_out')->default(0);
            $table->unsignedInteger('reward_olo_in')->default(0);
            $table->unsignedInteger('reward_olo_out')->default(0);
            $table->unsignedInteger('battle_ignored')->default(0);
            $table->unsignedInteger('battle_draw')->default(0);
            $table->unsignedTinyInteger('battle_win_con')->default(0); //连续赢大乱斗
            $table->unsignedTinyInteger('battle_lose_con')->default(0); //连续输大乱斗
            $table->unsignedInteger('delete_posts_num')->default(0);
            $table->unsignedInteger('posts_num')->default(0);
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
        Schema::dropIfExists('user_medal_record');
    }
}
