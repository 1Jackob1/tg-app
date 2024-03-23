<?php

declare(strict_types=1);

namespace App\Client\CurrencyBean\Response;

use Carbon\CarbonImmutable;

class CurrencyBeaconLatestResponse
{
    public CarbonImmutable $date;
    public string $base;

    /**
     * @var array<string, string>
     */
    public array $rates = [];
}
