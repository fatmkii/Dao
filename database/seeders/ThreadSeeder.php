<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Thread;

class ThreadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Thread::factory(20)->create();
        Thread::create([
            'forum_id' => 1,
            'nickname' => '管理员',
            'sub_id' => 1,
            'title_color' => '#fd7e14',
            'title' => '这是一个管理员公告',
        ]);
        Thread::factory(100)->create();
    }
}
