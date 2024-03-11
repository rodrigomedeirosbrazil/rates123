<?php

namespace App\Scraper;

use App\Scraper\Contracts\ScraperContract;
use App\Scraper\DTOs\DayPriceDTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AirbnbScraper extends Scraper implements ScraperContract
{
    public string $endpoint = '/airbnb/prices';

    public function getPrices(string $url, CarbonInterface $from, int $days): Collection
    {
        $response = Http::timeout($this->timeout)
            ->get(
                config('app.scrap.url') . $this->endpoint,
                [
                    'url' => $url,
                    'fromDate' => $from->toDateString(),
                    'days' => $days,
                ]
            );

        $responsePrices = $response->json();

        return collect($responsePrices)
            ->filter(fn ($responsePrice) => $this->validatePrice($responsePrice))
            ->map(fn ($price) => $this->parsePrice($price))
            ->sortBy('checkin')
            ->slice(0, $days);
    }

    public function parsePrice(array $responsePrice): DayPriceDTO
    {
        return new DayPriceDTO(
            checkin: Carbon::parse(data_get($responsePrice, 'checkin')),
            price: data_get($responsePrice, 'price') ?? 0,
            available: data_get($responsePrice, 'available', false),
            extra: data_get($responsePrice, 'extra', []),
        );
    }

    public function validatePrice(array $responsePrice): bool
    {
        $validator = Validator::make($responsePrice, [
            'price' => 'nullable|numeric',
            'checkin' => 'required|date',
            'available' => 'nullable|boolean',
        ]);

        if (! $validator->fails()) {
            return true;
        }

        Log::warning(
            'Invalid price data',
            [
                'errors' => $validator->errors()->toArray(),
                'payload' => $responsePrice,
                'platform' => 'booking',
            ]
        );

        return false;
    }
}
