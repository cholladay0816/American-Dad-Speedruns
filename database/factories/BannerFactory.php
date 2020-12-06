<?php

namespace Database\Factories;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Banner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->sentence( 7),
            'user_id' => User::first()->id,
            'expiration' => now()->addDays(7)->toDateTimeString()
        ];
    }
}
