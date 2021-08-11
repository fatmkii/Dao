<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'forum_id' => $this->faker->randomElement(array('1', '12', '40')),
            'thread_id' => $this->faker->randomNumber($nbDigits = 1)+100000,
            'floor' => $this->faker->randomNumber($nbDigits = 2),
            'random_head' => $this->faker->randomDigit(),
            'content' => $this->faker->text(200),
        ];
    }
}