<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGambleQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gamble_questions', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->integer('thread_id')->index();
            $table->text('title');
            $table->timestamp('end_date')->nullable();//在阿里云rds上，不知道为什么not null的话一定会default current_timestamp
            $table->unsignedInteger('bet_total')->default(0);
            $table->unsignedInteger('olo_total')->default(0);
            $table->integer('result_option_id')->nullable();
            $table->tinyInteger('is_closed')->default(0); //0=进行中；1=已正常结束；2=已中止
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
        Schema::dropIfExists('gamble_questions');
    }
}
