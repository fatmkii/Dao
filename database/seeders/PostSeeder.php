<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use Faker\Factory;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Post::factory(5000)->create();
        $faker = Factory::create();
        for ($i = 0; $i < 198; $i++) {
            DB::table('posts_1')->insert([
                'created_binggan'=>"cX8fg1NTm",
                'created_IP'=>'127.0.0.1',
                'forum_id' => 2,
                'thread_id' => 10001,
                'floor' => $i + 1,
                'random_head' => $faker->randomDigit(),
                'content' => $faker->text(20),
            ]);
            DB::table('threads')->where('id', 10063)->increment('posts_num');
        }
    }
}
