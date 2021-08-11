<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id()->startingValue(100001);
            $table->string('binggan')->unique()->index();
            $table->string('nickname')->nullable(); //自定义昵称
            $table->string('password')->nullable();
            $table->boolean('is_banned')->default(0); //0=正常；1=banned；
            $table->timestamp('locked_until')->nullable(); //被暂时锁定直到某时间
            $table->tinyInteger('admin')->default(0); //1=一般管理员，99=超管
            $table->integer('coin')->default(0); //通用货币
            $table->timestamp('last_login')->nullable();
            $table->string('created_ip')->nullable();
            $table->boolean('use_pingbici')->default(0); 
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
        Schema::dropIfExists('users');
    }
}
