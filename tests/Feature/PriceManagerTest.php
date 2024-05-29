<?php

use App\Enums\PriceNotificationTypeEnum;
use App\Managers\PriceManager;
use App\Models\PriceNotification;
use App\Models\Property;
use App\Models\PropertyProperty;
use App\Models\User;
use App\Models\UserProperty;

it('should create a price notification text', function () {
    $user = User::factory()->create();

    $property1 = Property::factory()->create(['name' => 'Property 1']);
    $property2 = Property::factory()->create(['name' => 'Property 2']);
    $property3 = Property::factory()->create(['name' => 'Property 3']);
    $property4 = Property::factory()->create(['name' => 'Property 4']);

    UserProperty::factory()->create([
        'user_id' => $user->id,
        'property_id' => $property1->id,
    ]);

    UserProperty::factory()->create([
        'user_id' => $user->id,
        'property_id' => $property2->id,
    ]);

    PropertyProperty::factory()->create([
        'property_id' => $property1->id,
        'followed_property_id' => $property3->id,
    ]);

    PropertyProperty::factory()->create([
        'property_id' => $property1->id,
        'followed_property_id' => $property4->id,
    ]);

    PropertyProperty::factory()->create([
        'property_id' => $property2->id,
        'followed_property_id' => $property4->id,
    ]);

    PriceNotification::factory()->create([
        'property_id' => $property3->id,
        'checkin' => now()->addDays(1),
        'type' => PriceNotificationTypeEnum::PriceUp,
        'average_price' => 100,
        'before' => 50,
        'after' => 100,
        'created_at' => today(),
        'updated_at' => today(),
    ]);

    PriceNotification::factory()->create([
        'property_id' => $property4->id,
        'checkin' => now()->addDays(1),
        'type' => PriceNotificationTypeEnum::PriceUp,
        'average_price' => 100,
        'before' => 50,
        'after' => 100,
        'created_at' => today(),
        'updated_at' => today(),
    ]);

    $priceNotifications = (new PriceManager())->getUserPriceNotificationsByCreatedAt($user, today());

    expect($priceNotifications->count())->toBe(2);
    expect($priceNotifications->first()->count())->toBe(2);
    expect($priceNotifications->last()->count())->toBe(1);

    $priceNotificationsText = (new PriceManager())->buildPriceNotificationsTextList($priceNotifications);

    expect($priceNotificationsText)->toBeString();
});
