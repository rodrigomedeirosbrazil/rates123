<?php

return [
    'booking' => [
        'scrap_days' => env('BOOKING_SCRAP_DAYS', 180),
        'scrap_days_session' => env('BOOKING_SCRAP_DAYS', 180),
    ],

    'airbnb' => [
        'scrap_days' => env('AIRBNB_SCRAP_DAYS', 60),
        'scrap_days_session' => env('BOOKING_SCRAP_DAYS', 30),
    ],
];
