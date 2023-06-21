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
            $table->id()->from(100001);
            $table->string('binggan')->unique()->index();
            $table->string('nickname')->nullable(); //自定义昵称
            $table->string('password')->nullable();
            $table->boolean('is_custom')->default(0); //0=普通饼干；1=定制饼干；
            $table->boolean('is_banned')->default(0); //0=正常；1=banned；
            $table->timestamp('locked_until')->nullable(); //被暂时锁定直到某时间
            $table->tinyInteger('locked_count')->default(0);//被锁定的计数器，超过3次就封饼干
            $table->tinyInteger('admin')->default(0); //1=一般管理员，99=超管
            $table->unsignedInteger('coin')->default(0); //通用货币
            $table->timestamp('last_login')->nullable();
            $table->string('created_ip')->nullable();
            $table->string('created_UUID')->nullable();
            $table->string('created_location')->nullable();
            $table->boolean('use_pingbici')->default(0); 
            $table->boolean('new_msg')->default(0); 
            $table->tinyInteger('user_lv')->default(0);
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
