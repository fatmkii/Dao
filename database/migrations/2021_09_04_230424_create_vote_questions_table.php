<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVoteQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vote_questions', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->integer('thread_id')->index();
            $table->text('title');
            $table->timestamp('end_date');
            $table->integer('vote_total')->default(0);
            $table->boolean('multiple')->default(0); //多选，还没做
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
        Schema::dropIfExists('vote_questions');
    }
}
