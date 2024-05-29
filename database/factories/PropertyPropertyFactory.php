<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PropertyPropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => $this->faker->numberBetween(1, 100),
            'followed_property_id' => $this->faker->numberBetween(1, 100),
        ];
    }
}
