<?php

namespace Database\Factories;

use App\Enums\PriceNotificationTypeEnum;
use Illuminate\Database\Eloquent\Factories\Factory;

class PriceNotificationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'property_id' => $this->faker->numberBetween(1, 100),
            'checkin' => $this->faker->dateTimeBetween('now', '+1 year'),
            'type' => $this->faker->randomElement(array_keys(PriceNotificationTypeEnum::toArray())),
            'average_price' => $this->faker->randomFloat(2, 1, 1000),
            'before' => $this->faker->randomFloat(2, 1, 1000),
            'after' => $this->faker->randomFloat(2, 1, 1000),
        ];
    }
}
