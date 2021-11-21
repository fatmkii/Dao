<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGambleUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i = 1; $i < 3; $i++) {
            Schema::create('gamble_users_' . $i, function (Blueprint $table) {
                $table->id();
                $table->integer('user_id')->index();
                $table->integer('gamble_question_id')->index();
                $table->integer('option_id')->index();
                $table->integer('betting_olo');
                $table->float('odds', 8, 2, true)->nullable();
                $table->integer('win_olo')->nullable();
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
        Schema::dropIfExists('gamble_users');
    }
}
