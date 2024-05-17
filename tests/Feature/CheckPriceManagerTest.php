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
            minStay: 1,
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
            minStay: 1,
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

it('should group consecutive unavailable dates', function () {
    $property = Property::factory()->create();

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(1)->startOfDay(),
            'price' => 1,
            'available' => true,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(1)->startOfDay(),
            'price' => 0,
            'available' => false,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(2)->startOfDay(),
            'price' => 0,
            'available' => false,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(3)->startOfDay(),
            'price' => 1,
            'available' => true,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(4)->startOfDay(),
            'price' => 0,
            'available' => false,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(5)->startOfDay(),
            'price' => 1,
            'available' => true,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(6)->startOfDay(),
            'price' => 0,
            'available' => false,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(7)->startOfDay(),
            'price' => 0,
            'available' => false,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(8)->startOfDay(),
            'price' => 0,
            'available' => false,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(9)->startOfDay(),
            'price' => 1,
            'available' => true,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDays(10)->startOfDay(),
            'price' => 0,
            'available' => false,
            'created_at' => today()->startOfDay(),
            'updated_at' => today()->startOfDay(),
        ]);

    $rates = (new CheckPriceManager())->getGroupUnavailableConsecutiveDates($property->id);

    expect($rates->count())->toBe(4);

    expect($rates[0])->toHaveCount(2);

    expect($rates[1])->toHaveCount(1);

    expect($rates[2])->toHaveCount(3);

    expect($rates[3])->toHaveCount(1);
});
