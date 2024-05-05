<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class RateFactory extends Factory
{
    public function definition(): array
    {
        $available = $this->faker->boolean(90);

        return [
            'price' => $available ? $this->faker->randomFloat(2, 10, 1000) : 0,
            'checkin' => $this->faker->dateTimeBetween('now', '+1 year'),
            'available' => $available,
            'extra' => [],
        ];
    }
}
