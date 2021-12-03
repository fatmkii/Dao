<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBattleMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('battle_messages', function (Blueprint $table) {
            $table->id();
            $table->integer('battle_id')->index();
            $table->string('chara_url')->nullable();
            $table->tinyInteger('message_type')->default(0); //0=系统文字；1=发起者说；2=挑战者说；
            $table->string('message')->nullable();
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
        Schema::dropIfExists('battle_messages');
    }
}
