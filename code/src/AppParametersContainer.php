<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class AppParametersContainer
{
    public function __construct(
        #[Autowire('%env(APP_CURRENCY_BEACON_BASE_URI)%')]
        public readonly string $currencyBeaconBaseUri,
        #[Autowire('%env(APP_CURRENCY_BEACON_API_KEY)%')]
        public readonly string $currencyBeaconApiKey,
        #[Autowire('%env(APP_TELEGRAM_BASE_URI)%')]
        public readonly string $telegramBaseUri,
        #[Autowire('%env(APP_TELEGRAM_API_KEY)%')]
        public readonly string $telegramApiKey,
    ) {}
}
