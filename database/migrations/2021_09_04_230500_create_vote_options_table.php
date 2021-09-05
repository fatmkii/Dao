<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoteOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vote_options', function (Blueprint $table) {
<<<<<<< Updated upstream
            $table->id();
            $table->integer('vote_question_id')->index();
            $table->string('option_text');
            $table->integer('vote_total');
=======
            $table->id()->startingValue(10001);
            $table->integer('vote_question_id')->index();
            $table->string('option_text');
            $table->integer('vote_total')->default(0);
>>>>>>> Stashed changes
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
        Schema::dropIfExists('vote_options');
    }
}
