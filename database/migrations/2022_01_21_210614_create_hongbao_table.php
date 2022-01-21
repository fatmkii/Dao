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
        Schema::create('hongbao_record', function (Blueprint $table) {
            $table->id();
            $table->integer('thread_id');
            $table->integer('post_id');
            $table->integer('user_id');
            $table->string('created_binggan');
            $table->integer('rand_num');
            $table->integer('olo');
            $table->timestamp('created_at');
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
