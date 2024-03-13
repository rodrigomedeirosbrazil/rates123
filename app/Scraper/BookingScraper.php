<?php

namespace App\Scraper;

use App\Scraper\Contracts\ScraperContract;
use App\Scraper\DTOs\DayPriceDTO;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BookingScraper extends Scraper implements ScraperContract
{
    public string $endpoint = '/booking/prices';

    public function getPrices(string $url, CarbonInterface $from, int $days): Collection
    {
        $to = $from->addDays($days);
        $months = $from->diffInMonths($to) === 0 ? 1 : $from->diffInMonths($to);

        $response = Http::timeout($this->timeout)
            ->get(
                config('app.scrap.url') . $this->endpoint,
                [
                    'url' => $url,
                    'pages' => $months,
                ]
            );

        if (! $response->ok()) {
            Log::error(
                'Failed to get prices',
                [
                    'url' => $url,
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'days' => $days,
                    'response' => $response->json(),
                    'platform' => 'booking',
                ]
            );

            return collect();
        }

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
            price: human_readable_size_to_int(
                data_get($responsePrice, 'avgPriceFormatted') ?? '0'
            ),
            available: data_get($responsePrice, 'available', false),
            extra: [
                'minLengthOfStay' => data_get($responsePrice, 'minLengthOfStay'),
            ],
        );
    }

    public function validatePrice(array $responsePrice): bool
    {
        $validator = Validator::make($responsePrice, [
            'avgPriceFormatted' => 'nullable|string',
            'checkin' => 'required|date',
            'available' => 'nullable|boolean',
            'minLengthOfStay' => 'nullable|integer',
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
