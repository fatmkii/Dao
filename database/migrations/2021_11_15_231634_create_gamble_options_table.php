<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGambleOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gamble_options', function (Blueprint $table) {
            $table->id()->from(10001);
            $table->integer('gamble_question_id')->index();
            $table->string('option_text');
            $table->unsignedInteger('bet_total')->default(0);
            $table->unsignedInteger('olo_total')->default(0);
            $table->float('odds', 8, 2, true)->default(93.00); // 默认最高赔率93
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
        Schema::dropIfExists('gamble_options');
    }
}
