<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubtitlesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('subtitles')->insert([
            'value' => '[公告]',
            'text' => '[公告]',
            'for_admin' => true,
        ]);

        DB::table('subtitles')->insert([
            'value' => '[闲聊]',
            'text' => '[闲聊]',
            'for_admin' => false,
        ]);

        DB::table('subtitles')->insert([
            'value' => '[专楼]',
            'text' => '[专楼]',
            'for_admin' => false,
        ]);
    }
}
