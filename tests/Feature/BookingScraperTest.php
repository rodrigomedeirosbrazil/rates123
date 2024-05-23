<?php

use App\Scraper\BookingScraper;
use App\Scraper\DTOs\DayPriceDTO;
use App\Scraper\Scraper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

it('check contract', function () {
    $scraper = new BookingScraper();

    expect($scraper)->toBeInstanceOf(Scraper::class);
});

it('should get prices', function () {
    $bookingPrices = [
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

    $scraper = new BookingScraper();

    $url = 'https://www.booking.com/hotel/pt/terras-de-santa-cristina.en-gb.html';
    $from = Carbon::parse('2022-01-01');
    $days = 10;

    $to = $from->copy()->addDays($days);
    $months = $from->diffInMonths($to) === 0 ? 1 : $from->diffInMonths($to);

    $urlToHttpFake = config('app.scrap.url')
        . '/booking/prices?url='
        . urlencode($url)
        . "&pages={$months}";

    Http::fake([
        $urlToHttpFake => Http::response($bookingPrices),
    ]);

    $prices = $scraper->getPrices($url, $from, $days);

    expect($prices)->toBeInstanceOf(Collection::class);
    expect($prices->count())->toBe(count($bookingPrices));
    expect($prices->first())->toBeInstanceOf(DayPriceDTO::class);
});

it('should parse price object from booking', function () {
    $scraper = new BookingScraper();

    $responsePrice = [
        'checkin' => '2022-01-01',
        'avgPriceFormatted' => '1K',
        'available' => true,
        'minLengthOfStay' => 1,
    ];

    $dayPrice = $scraper->parsePrice($responsePrice);

    expect($dayPrice->checkin)->toBeInstanceOf(Carbon::class);
    expect($dayPrice->checkin->format('Y-m-d'))->toBe('2022-01-01');
    expect($dayPrice->price)->toBe(1000.0);
    expect($dayPrice->available)->toBeTrue();
    expect($dayPrice->minStay)->toBe(1);
});

it('should validate price object', function () {
    $scraper = new BookingScraper();

    $responsePrice = [
        'checkin' => '2022-01-01',
        'avgPriceFormatted' => '1K',
        'available' => true,
        'minLengthOfStay' => 1,
    ];

    expect($scraper->validatePrice($responsePrice))->toBeTrue();
});

it('should fail to validate price object', function () {
    $scraper = new BookingScraper();

    $responsePrice = [
        'checkin' => '2022-54-01',
        'avgPriceFormatted' => '1K',
        'available' => true,
        'minLengthOfStay' => 1,
    ];

    expect($scraper->validatePrice($responsePrice))->toBeFalse();
});

it('should get price details', function () {
    $scraper = new BookingScraper();

    $url = 'https://www.booking.com/hotel/pt/terras-de-santa-cristina.en-gb.html';
    $from = Carbon::parse('2022-01-01');
    $to = Carbon::parse('2022-01-03');

    $urlToHttpFake = config('app.scrap.url')
        . '/booking/unavailable-prices?url='
        . urlencode($url)
        . "&checkin={$from->toDateString()}&checkout={$to->toDateString()}";

    Http::fake([
        $urlToHttpFake => Http::response(['price' => 1000]),
    ]);

    $prices = $scraper->getPriceDetail($url, $from, $to);

    expect($prices->count())->toBe(2);
    expect($prices->first()->price)->toBe(500.0);
    expect($prices->last()->price)->toBe(500.0);
});
