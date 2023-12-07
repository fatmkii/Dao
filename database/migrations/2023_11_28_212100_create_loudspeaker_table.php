<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoudspeakerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loudspeakers', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('sub_id')->default(0); //用于重要度排序的（公告为99）
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('created_binggan')->nullable();
            $table->string('content');
            $table->string('color')->nullable();
            $table->unsignedInteger('thread_id')->nullable(); //链接到相应的主题
            $table->timestamp('effective_date');
            $table->timestamp('expire_date');
            $table->softDeletes();
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
        Schema::dropIfExists('loudspeaker');
    }
}
