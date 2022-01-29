<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePingbicisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pingbicis', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unique();
            $table->json('title_pingbici')->nullable();
            $table->json('content_pingbici')->nullable();
            $table->json('fjf_pingbici')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pingbicis');
    }
}
