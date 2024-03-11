<?php

use App\Scraper\AirbnbScraper;
use App\Scraper\DTOs\DayPriceDTO;
use App\Scraper\Scraper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('check contract', function () {
    $scraper = new AirbnbScraper();

    expect($scraper)->toBeInstanceOf(Scraper::class);
});

it('should get prices', function () {
    $airbnbPrices = [
        [
            'checkin' => '2022-01-01',
            'avgPriceFormatted' => '1K',
            'available' => true,
            'minLengthOfStay' => 1,
        ],
        [
            'checkin' => '2022-01-02',
            'avgPriceFormatted' => '1 M',
            'available' => false,
            'minLengthOfStay' => 3,
        ],
    ];

    $scraper = new AirbnbScraper();

    $url = 'https://www.airbnb.com/rooms/123456';
    $from = Carbon::parse('2022-01-01');
    $days = 10;

    $urlToHttpFake = config('app.scrap.url')
        . '/airbnb/prices?url='
        . urlencode($url)
        . "&fromDate={$from->toDateString()}"
        . "&days={$days}";

    Http::fake([
        $urlToHttpFake => Http::response($airbnbPrices),
    ]);

    $prices = $scraper->getPrices($url, $from, $days);

    expect($prices)->toBeInstanceOf(Collection::class);
    expect($prices->count())->toBe(count($airbnbPrices));
    expect($prices->first())->toBeInstanceOf(DayPriceDTO::class);
});

it('should parse price object from airbnb', function () {
    $scraper = new AirbnbScraper();

    $responsePrice = [
        'checkin' => '2022-01-01',
        'price' => 100,
        'available' => true,
    ];

    $price = $scraper->parsePrice($responsePrice);

    expect($price->checkin)->toBeInstanceOf(Carbon::class);
    expect($price->checkin->toDateString())->toBe('2022-01-01');
    expect($price->price)->toBe(100.0);
    expect($price->available)->toBeTrue();
    expect($price->extra)->toBe([]);
});

it('should validate price object from airbnb', function () {
    $scraper = new AirbnbScraper();

    $responsePrice = [
        'checkin' => '2022-01-01',
        'price' => 100,
        'available' => true,
    ];

    expect($scraper->validatePrice($responsePrice))->toBeTrue();
});

it('should log invalid price object from airbnb', function () {
    $scraper = new AirbnbScraper();

    $responsePrice = [
        'checkin' => '2022-01-01',
        'price' => 'invalid',
        'available' => true,
    ];

    expect($scraper->validatePrice($responsePrice))->toBeFalse();
});
