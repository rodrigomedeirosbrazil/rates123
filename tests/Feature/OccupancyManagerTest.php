<?php

use App\Managers\OccupancyManager;
use App\Models\Occupancy;
use App\Models\Property;
use App\Scraper\DTOs\OccupancyDTO;

it('should notify occupancy change', function () {
    $occupancyManager = new OccupancyManager();

    expect($occupancyManager->shouldNotifyOccupancyChange(9, 10))->toBeTrue();
    expect($occupancyManager->shouldNotifyOccupancyChange(9, 13))->toBeTrue();
    expect($occupancyManager->shouldNotifyOccupancyChange(9, 19))->toBeTrue();

    expect($occupancyManager->shouldNotifyOccupancyChange(1, 9))->toBeFalse();
    expect($occupancyManager->shouldNotifyOccupancyChange(21, 29))->toBeFalse();
});

it('should create occupancy when have new data', function () {
    $occupancyManager = new OccupancyManager();

    $occupancies = collect([
        new OccupancyDTO(
            checkin: today()->addDays(10),
            totalRooms: 10,
            occupiedRooms: 5,
        ),
    ]);

    $property = Property::factory()->create();

    $occupancyModel = Occupancy::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => today()->addDays(10),
            'total_rooms' => 10,
            'occupied_rooms' => 1,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

    expect($occupancyModel->updated_at->isToday())->toBeFalse();
    expect(Occupancy::all()->count())->toBe(1);

    $occupancyManager->processOccupancy($property->id, $occupancies);

    $occupancyModel->refresh();

    expect($occupancyModel->updated_at->isToday())->toBeFalse();

    expect(Occupancy::all()->count())->toBe(2);
});

it('should update last occupancy', function () {
    $occupancyManager = new OccupancyManager();

    $occupancies = collect([
        new OccupancyDTO(
            checkin: today()->addDays(10),
            totalRooms: 10,
            occupiedRooms: 1,
        ),
    ]);

    $property = Property::factory()->create();

    $occupancyModel = Occupancy::factory()
        ->create([
            'property_id' => $property->id,
            'checkin' => today()->addDays(10),
            'total_rooms' => 10,
            'occupied_rooms' => 1,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

    expect($occupancyModel->updated_at->isToday())->toBeFalse();
    expect(Occupancy::all()->count())->toBe(1);

    $occupancyManager->processOccupancy($property->id, $occupancies);

    $occupancyModel->refresh();

    expect($occupancyModel->updated_at->isToday())->toBeTrue();

    expect(Occupancy::all()->count())->toBe(1);
});
