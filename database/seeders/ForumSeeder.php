<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Forum;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Forum::create([
            'id' =>  1,
            'name' => '有爱讨论岛',
            'is_anonymous' => 1,
            'description' => '存档，专楼，想留下记录的请来此处',
        ]);
        Forum::create([
            'id' =>  12,
            'name' => '海滨乐园岛',
            'is_anonymous' => 1,
            'description' => '新来的朋友请看此处，饼干验证，杂物和无法分类的请来此岛',
        ]);
        Forum::create([
            'id' =>  419,
            'name' => '发春大舞厅',
            'is_nissin' => 2,
            'is_anonymous' => 0,
            'accessible_coin' => 10000,
            'description' => '发春大舞台，想发春你就来。24-72小时锁贴制，不带走一片云彩',
            'default_heads' => 2,
            'banners' => json_encode(array(
                'https://i.loli.net/2021/05/05/aJEPC2RMbWoYVpL.jpg',
                'https://i.loli.net/2021/05/05/9mk3s2WOlMZxGYz.gif',
                'https://ftp.bmp.ovh/imgs/2021/06/5481dfe13988a764.jpg',
                'https://s3.jpg.cm/2021/06/30/IWjLSG.jpg',
            ))
        ]);
        Forum::create([
            'id' =>  914,
            'name' => '日清岛',
            'is_nissin' => 1,
            'is_anonymous' => 1,
            'description' => '8点日清，禁sz禁sq禁挂素人',
        ]);
        Forum::create([
            'id' =>  2811,
            'name' => '余生皆假期',
            'is_nissin' => 0,
            'is_anonymous' => 0,
            'description' => '私人岛，请勿打扰',
        ]);
    }
}
