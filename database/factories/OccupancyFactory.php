<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OccupancyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => $this->faker->numberBetween(1, 100),
            'checkin' => $this->faker->date(),
            'total_rooms' => $this->faker->numberBetween(1, 100),
            'occupied_rooms' => $this->faker->numberBetween(1, 100),
        ];
    }
}
