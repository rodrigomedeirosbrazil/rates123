<?php

namespace App\Scraper;

use App\Scraper\DTOs\OccupancyDTO;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HitsScraper
{
    public string $endpoint = '/hits/occupancy';
    public int $timeout = 500;

    public function getOccupancies(string $propertyName, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $response = Http::timeout($this->timeout)
            ->get(
                config('app.scrap.url') . $this->endpoint,
                [
                    'propertyName' => $propertyName,
                    'fromDate' => $from->toDateString(),
                    'toDate' => $to->toDateString(),
                ]
            );

        if (! $response->ok()) {
            Log::error(
                'Failed to get occupancies',
                [
                    'from' => $from->toDateString(),
                    'to' => $to->toDateString(),
                    'response' => $response->json(),
                    'platform' => 'hits',
                ]
            );

            return collect();
        }

        $responseOccupancies = $response->json();

        return collect($responseOccupancies)
            ->filter(fn ($responseOccupancy) => $this->validateOccupancy($responseOccupancy))
            ->map(fn ($responseOccupancy) => $this->parseOccupancy($responseOccupancy))
            ->sortBy('checkin');
    }

    public function parseOccupancy(array $responseOccupancy): OccupancyDTO
    {
        return new OccupancyDTO(
            checkin: Carbon::parse(data_get($responseOccupancy, 'Date')),
            totalRooms: data_get($responseOccupancy, 'Uhs', 0),
            occupiedRooms: data_get($responseOccupancy, 'Occ', 0),
        );
    }

    public function validateOccupancy(array $responseOccupancy): bool
    {
        $validator = Validator::make($responseOccupancy, [
            'Uhs' => 'required|integer',
            'Occ' => 'required|integer',
            'Date' => 'required|date',
        ]);

        if (! $validator->fails()) {
            return true;
        }

        Log::warning(
            'Invalid occupancy data',
            [
                'errors' => $validator->errors()->toArray(),
                'payload' => $responseOccupancy,
                'platform' => 'hits',
            ]
        );

        return false;
    }
}
