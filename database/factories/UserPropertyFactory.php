<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPropertyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 100),
            'property_id' => $this->faker->numberBetween(1, 100),
            'role' => $this->faker->randomElement(array_keys(RoleEnum::toArray())),
        ];
    }
}
