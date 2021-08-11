<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forums', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('sub_id')->unsigned()->default(0)->index(); //用来排序的。
            $table->string('name');
            $table->string('description');
            $table->tinyInteger('status')->default('1');
            $table->boolean('is_anonymous')->default(0);
            $table->Integer('accessible_coin')->default(0); //最低可进入的olo
            $table->tinyInteger('is_nissin')->default(0); //0=不日清；1=按8点日清；2=按24小时日清
            $table->json('banners')->nullable();
            $table->tinyInteger('default_heads')->default(1); //默认的头像组
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('forums');
    }
}
