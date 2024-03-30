<?php

use Carbon\Carbon;

if (!function_exists('only_numbers')) {
    function only_numbers(?string $data): ?string
    {
        if (is_null($data)) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $data);
    }
}

if (!function_exists('format_date_with_weekday')) {
    function format_date_with_weekday(?string $data): ?string
    {
        if (is_null($data)) {
            return null;
        }

        return Carbon::parse($data)->translatedFormat('D, d M y');
    }
}

if (!function_exists('human_readable_size_to_int')) {
    function human_readable_size_to_int(string $value): int
    {
        $number = (float) preg_replace('/[^0-9\.]/', '', $value);

        $prefix = strtolower(preg_replace('/[^tgmk]/i', '', $value));

        switch ($prefix) {
            case 't':
                $number *= 1000;
                // no break
            case 'g':
                $number *= 1000;
                // no break
            case 'm':
                $number *= 1000;
                // no break
            case 'k':
                $number *= 1000;
        }

        return (int) $number;
    }
}
