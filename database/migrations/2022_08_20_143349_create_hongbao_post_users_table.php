<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHongbaoPostUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hongbao_post_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hongbao_post_id')->constrained('hongbao_post');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedInteger('olo');
            $table->unsignedInteger('floor');
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
        Schema::dropIfExists('hongbao_post_users');
    }
}
