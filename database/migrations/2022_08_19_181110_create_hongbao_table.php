<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHongbaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hongbao', function (Blueprint $table) {
            $table->id()->startingValue(10001);
            $table->foreignId('thread_id')->constrained();
            $table->unsignedSmallInteger('num_total');
            $table->unsignedInteger('olo_total');
            $table->unsignedSmallInteger('num_remains');
            $table->unsignedInteger('olo_remains');
            $table->unsignedTinyInteger('type')->default(1); //1=口令红包
            $table->string('key_word');
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
        Schema::dropIfExists('hongbao');
    }
}
