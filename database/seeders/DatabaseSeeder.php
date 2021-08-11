<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserSeeder::class);
        $this->call(ForumSeeder::class);
        // $this->call(ThreadSeeder::class);
        // $this->call(PostSeeder::class);
        $this->call(EmojiSeeder::class);
        $this->call(SubtitlesSeeder::class);
        $this->call(RandomHeadsSeeder::class);
    }
}
