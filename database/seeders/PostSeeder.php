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
                'forum_id' => 12,
                'thread_id' => 10063,
                'floor' => $i + 1,
                'random_head' => $faker->randomDigit(),
                'content' => $faker->text(200),
            ]);
            DB::table('threads')->where('id', 10063)->increment('posts_num');
        }
    }
}
