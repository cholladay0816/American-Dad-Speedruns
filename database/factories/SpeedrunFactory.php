<?php

namespace Database\Factories;

use App\Models\Speedrun;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeedrunFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Speedrun::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 1,
            'url' => 'https://youtu.be/' . $this->faker->unique()->regexify('[A-Za-z0-9]{8}'),
            'time' => $this->faker->numberBetween(1, 30),
            'created_at' => now(),
            'updated_at' => now()
        ];
    }
}
