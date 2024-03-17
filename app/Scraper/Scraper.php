<?php

namespace App\Scraper;

use App\Scraper\Contracts\ScraperContract;

abstract class Scraper implements ScraperContract
{
    public int $timeout = 500;
}
