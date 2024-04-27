<?php

use App\Managers\OccupancyManager;

it('should notify occupancy change', function () {
    $occupancyManager = new OccupancyManager();

    expect($occupancyManager->shouldNotifyOccupancyChange(9, 10))->toBeTrue();
    expect($occupancyManager->shouldNotifyOccupancyChange(9, 13))->toBeTrue();
    expect($occupancyManager->shouldNotifyOccupancyChange(9, 19))->toBeTrue();

    expect($occupancyManager->shouldNotifyOccupancyChange(1, 9))->toBeFalse();
    expect($occupancyManager->shouldNotifyOccupancyChange(21, 29))->toBeFalse();
});
