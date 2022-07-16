<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIncomeStatementTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        for ($i = 2022; $i < 2024; $i++) {
            Schema::create('income_statement_' . $i, function (Blueprint $table) {
                $table->id()->startingValue(1000001);
                $table->timestamp('created_at')->nullable();
                $table->integer('olo');
                $table->unsignedInteger('user_id')->index();
                $table->string('binggan');
                $table->unsignedInteger('user_id_target')->nullable();
                $table->string('binggan_target')->nullable();
                $table->string('content')->nullable();
                $table->unsignedInteger('thread_id')->nullable();
                $table->string('thread_title')->nullable();
                $table->unsignedInteger('post_id')->nullable();
                $table->unsignedInteger('floor')->nullable();             
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        for ($i = 2022; $i < 2024; $i++) {
            Schema::dropIfExists('income_statement_' . $i);
        }
    }
}
