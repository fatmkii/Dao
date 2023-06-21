<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGlobalSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('global_setting', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->json('value');
        });
        DB::table('global_setting')->insert(['key' => 'new_binggan', 'value' => json_encode(true)]); //是否开启申请饼干的全局变量
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('GlobalSetting');
    }
}
