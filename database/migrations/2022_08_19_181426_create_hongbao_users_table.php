<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHongbaoUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hongbao_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hongbao_id')->constrained('hongbao');
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedInteger('olo');
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
        Schema::dropIfExists('hongbao_users');
    }
}
