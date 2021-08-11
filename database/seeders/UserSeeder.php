<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'id' =>  1,
            'binggan' => 'fat',
            'admin' => 99,
        ]);
        User::factory(10)->create();
    }
}
