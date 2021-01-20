<?php

namespace Database\Factories;

use App\Models\Platform;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlatformFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Platform::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->unique()->sentence(3);
        return [
            'title' => $name,
            'name' => Str::slug($name),
        ];
    }
}
