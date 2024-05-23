<?php

use App\Enums\PriceNotificationTypeEnum;
use App\Managers\CheckPriceManager;
use App\Models\PriceNotification;
use App\Models\Property;
use App\Models\Rate;
use App\Scraper\DTOs\DayPriceDTO;

it('should create a price notification with price up', function () {
    $property = Property::factory()->create();

    Rate::factory()
        ->create(
            [
                'property_id' => $property->id,
                'checkin' => now()->addDay(),
                'price' => 100,
                'available' => true,
                'created_at' => now()->subDay(),
                'updated_at' => now()->subDay(),
            ],
        );

    Rate::factory()
        ->create(
            [
                'property_id' => $property->id,
                'checkin' => now()->addDay(),
                'price' => 200,
                'available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

    (new CheckPriceManager())->checkPriceDate($property->id, now()->addDay());

    $notification = PriceNotification::query()
        ->wherePropertyId($property->id)
        ->whereDate('created_at', now())
        ->first();

    expect($notification->type)->toBe(PriceNotificationTypeEnum::PriceUp);
    expect(intval($notification->before))->toBe(100);
    expect(intval($notification->after))->toBe(200);
    expect(intval($notification->average_price))->toBe(100);
});


it('shouldnt create a price notification because old rate update', function () {
    $property = Property::factory()->create();

    Rate::factory()
        ->create(
            [
                'property_id' => $property->id,
                'checkin' => now()->addDay(),
                'price' => 100,
                'available' => true,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ],
        );

    Rate::factory()
        ->create(
            [
                'property_id' => $property->id,
                'checkin' => now()->addDay(),
                'price' => 200,
                'available' => true,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(2),
            ],
        );

    (new CheckPriceManager())->checkPriceDate($property->id, now()->addDay());

    expect(PriceNotification::count())->toBe(0);
});

it('shouldnt create a price notification because no exist old rate', function () {
    $property = Property::factory()->create();

    Rate::factory()
        ->create(
            [
                'property_id' => $property->id,
                'checkin' => now()->addDay(),
                'price' => 100,
                'available' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );

    (new CheckPriceManager())->checkPriceDate($property->id, now()->addDay());

    expect(PriceNotification::count())->toBe(0);
});

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

it('should delete unavailable rate and update last rate', function () {
    $price = rand(1, 100);

    $prices = collect([
        new DayPriceDTO(
            checkin: now()->addDay(),
            price: $price,
            available: true,
            minStay: 2,
            extra: [],
        ),
    ]);

    $property = Property::factory()->create();

    $rateUnavailable = Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDay(),
            'price' => 0,
            'available' => false,
            'min_stay' => 0,
            'created_at' => today(),
            'updated_at' => today(),
        ]);

    $lastAvailableRate = Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDay(),
            'price' => $price,
            'available' => true,
            'min_stay' => 1,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

    (new CheckPriceManager())->processPrices($property->id, $prices);

    expect(Rate::find($rateUnavailable->id))->toBeNull();

    $lastAvailableRate->refresh();

    expect($lastAvailableRate->updated_at->isToday())->toBeTrue();
    expect($lastAvailableRate->min_stay)->toBe($prices->first()->minStay);

    $rates = Rate::all()->count();

    expect($rates)->toBe(1);
});


it('should delete unavailable rate and create rate', function () {
    $price = rand(1, 100);

    $prices = collect([
        new DayPriceDTO(
            checkin: now()->addDay(),
            price: $price,
            available: true,
            minStay: 2,
            extra: [],
        ),
    ]);

    $property = Property::factory()->create();

    $rateUnavailable = Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDay(),
            'price' => 0,
            'available' => false,
            'min_stay' => 0,
            'created_at' => today(),
            'updated_at' => today(),
        ]);

    $lastAvailableRate = Rate::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => now()->addDay(),
            'price' => $price + 10,
            'available' => true,
            'min_stay' => 1,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

    (new CheckPriceManager())->processPrices($property->id, $prices);

    expect(Rate::find($rateUnavailable->id))->toBeNull();

    $lastAvailableRate->refresh();

    expect($lastAvailableRate->updated_at->isToday())->toBeFalse();
    expect($lastAvailableRate->min_stay)->toBe(1);

    expect(Rate::all()->count())->toBe(2);
});
