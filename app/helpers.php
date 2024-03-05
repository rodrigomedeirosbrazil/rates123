<?php

use Carbon\Carbon;

if (! function_exists('only_numbers')) {
    function only_numbers(?string $data): ?string
    {
        if (is_null($data)) {
            return null;
        }

        return preg_replace('/[^0-9]/', '', $data);
    }
}

if (! function_exists('format_date_with_weekday')) {
    function format_date_with_weekday(?string $data): ?string
    {
        if (is_null($data)) {
            return null;
        }

        return Carbon::parse($data)->format('l, d F Y');
    }
}
