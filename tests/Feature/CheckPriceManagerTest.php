<?php

use App\Managers\CheckPriceManager;
use App\Models\Property;
use App\Models\Rate;
use App\Scraper\DTOs\DayPriceDTO;

it('should update updated_at field on a rate', function () {
    $price = rand(1, 100);

    $prices = collect([
        new DayPriceDTO(
            checkin: now()->addDay(),
            price: $price,
            available: true,
            extra: [],
        ),
    ]);

    $property = Property::factory()->create();
    $rate = Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDay(),
            'price' => $price,
            'available' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

    expect($rate->updated_at->isToday())->toBeFalse();

    (new CheckPriceManager())->processPrices($property->id, $prices);

    $rate->refresh();

    expect($rate->updated_at->isToday())->toBeTrue();

    $rates = Rate::all()->count();

    expect($rates)->toBe(1);
});

it('should create a new rate', function () {
    $price = rand(1, 100);

    $prices = collect([
        new DayPriceDTO(
            checkin: now()->addDay(),
            price: $price,
            available: true,
            extra: [],
        ),
    ]);

    $property = Property::factory()->create();
    $rate = Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDay(),
            'price' => $price + 10,
            'available' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

    expect($rate->updated_at->isToday())->toBeFalse();

    (new CheckPriceManager())->processPrices($property->id, $prices);

    $rate->refresh();

    expect($rate->updated_at->isToday())->toBeFalse();

    $rates = Rate::all()->count();

    expect($rates)->toBe(2);
});
