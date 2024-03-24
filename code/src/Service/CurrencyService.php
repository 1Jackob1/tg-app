<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\CurrencyBean\CurrencyBeaconConnector;
use App\Client\CurrencyBean\Request\CurrencyBeaconLatestRequest;
use App\Client\CurrencyBean\Response\CurrencyBeaconLatestResponse;
use App\RequestDto\CurrencyExchangeRateActionRequestDto;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use RuntimeException;

readonly class CurrencyService
{
    public function __construct(
        private CurrencyBeaconConnector $currencyBeaconConnector,
        private CacheItemPoolInterface $currencyBeaconCache,
    ) {}

    public static function buildCurrencyPairCacheKey(string $base, string $to): string
    {
        return sprintf('BASE-%s#TO-%s', $base, $to);
    }

    /**
     * @param string $cacheKey
     *
     * @return array{base: string, to: string}
     */
    public static function getBaseAndToCurrencyFromCacheKey(string $cacheKey): array
    {
        $pattern = '/BASE-(?<base>\w{3})#TO-(?<to>\w{3})$/';
        $matches = [];

        $valid = preg_match($pattern, $cacheKey, $matches);

        if ($valid === false || $valid === 0) {
            throw new RuntimeException('Invalid cache key provided! Key: ' . $cacheKey);
        }

        return [
            'base' => $matches['base'],
            'to'   => $matches['to'],
        ];
    }

    public function getRate(CurrencyExchangeRateActionRequestDto $requestDto): CurrencyBeaconLatestResponse
    {
        $pairs = [];

        foreach ($requestDto->to as $to) {
            $pairs[] = self::buildCurrencyPairCacheKey(
                base: $requestDto->base,
                to: $to
            );
        }

        $atLeastOneMissing = false;
        $rates             = iterator_to_array($this->currencyBeaconCache->getItems($pairs));

        /** @var CacheItemInterface $cacheItem */
        foreach ($rates as $cacheItem) {
            $atLeastOneMissing = $atLeastOneMissing || !$cacheItem->isHit();
        }

        if ($atLeastOneMissing) {
            $this->setDataToCache(requestDto: $requestDto, cacheItems: $rates);
            $rates = $this->currencyBeaconCache->getItems($pairs);
        }

        $result = new CurrencyBeaconLatestResponse();

        foreach ($rates as $key => $cacheItem) {
            $result->rates[self::getBaseAndToCurrencyFromCacheKey($key)['to']] = $cacheItem->get();
        }

        $result->base = $requestDto->base;
        $result->date = CarbonImmutable::now();

        return $result;
    }

    /**
     * @param CurrencyExchangeRateActionRequestDto $requestDto
     * @param array<CacheItemInterface>            $cacheItems
     *
     * @return void
     */
    private function setDataToCache(CurrencyExchangeRateActionRequestDto $requestDto, array $cacheItems): void
    {
        $latestRequest = new CurrencyBeaconLatestRequest(
            base: $requestDto->base,
            symbols: $requestDto->to,
            connector: $this->currencyBeaconConnector,
        );

        $result    = $latestRequest->execute();
        $cacheData = [];

        /**
         * @var string $currencyCode
         * @var float  $rate
         */
        foreach ($result->rates as $currencyCode => $rate) {
            $pairCacheKey             = self::buildCurrencyPairCacheKey(base: $result->base, to: $currencyCode);
            $cacheData[$pairCacheKey] = number_format(
                num: $rate,
                decimals: 4,
                decimal_separator: ',',
                thousands_separator: '',
            );
        }

        foreach ($cacheItems as $cacheItem) {
            // TODO: raise exception
            $cacheItem->set($cacheData[$cacheItem->getKey()] ?? null);
            $cacheItem->expiresAt(Carbon::now()->addHour());

            $this->currencyBeaconCache->saveDeferred($cacheItem);
        }

        $this->currencyBeaconCache->commit();
    }
}
