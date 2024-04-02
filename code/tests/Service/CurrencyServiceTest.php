<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\AppParametersContainer;
use App\Client\CurrencyBeacon\CurrencyBeaconConnector;
use App\Client\CurrencyBeacon\Request\CurrencyBeaconLatestRequest;
use App\RequestDto\CurrencyExchangeRateActionRequestDto;
use App\Service\CurrencyService;
use App\Tests\AppTestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class CurrencyServiceTest extends AppTestCase
{
    public function testGetRate(): void
    {
        $mockClient = new MockClient([
            MockResponse::make(self::getStub('currencybeacon_rub_to_aed_thb_success_response.json')),
        ]);

        $requestDto       = new CurrencyExchangeRateActionRequestDto();
        $requestDto->base = 'RUB';
        $requestDto->to   = [
            'AED',
            'THB',
        ];

        $connector = self::getObjectFromContainer(CurrencyBeaconConnector::class);
        $connector->withMockClient($mockClient);

        $service = self::getObjectFromContainer(CurrencyService::class);

        $service->getRate($requestDto);

        $mockClient->assertSent('https://api.currencybeacon.com/v1/latest');
        $mockClient->assertSentCount(1, CurrencyBeaconLatestRequest::class);

        $lastRequest = $mockClient->getLastPendingRequest();
        $sentQuery   = $lastRequest->query()->all();

        self::assertArrayHasKey('symbols', $sentQuery);
        self::assertArrayHasKey('base', $sentQuery);
        self::assertArrayHasKey('api_key', $sentQuery);
        self::assertEquals('RUB', $sentQuery['base']);

        $expectedSymbols = ['THB', 'AED'];
        $actualSymbols   = explode(',', $sentQuery['symbols']);
        sort($expectedSymbols);
        sort($actualSymbols);
        $apiKey = self::getObjectFromContainer(AppParametersContainer::class)->currencyBeaconApiKey;

        self::assertEquals($expectedSymbols, $actualSymbols);
        self::assertEquals($apiKey, $sentQuery['api_key']);
    }

    public function testBuildCurrencyPairCacheKey(): void
    {
        $result = CurrencyService::buildCurrencyPairCacheKey('RUB', 'USD');

        self::assertEquals('BASE-RUB#TO-USD', $result);
    }

    public function testGetBaseAndToCurrencyFromCacheKey(): void
    {
        $result = CurrencyService::getBaseAndToCurrencyFromCacheKey('BASE-RUB#TO-USD');

        self::assertArrayHasKey('to', $result);
        self::assertArrayHasKey('base', $result);
        self::assertEquals('USD', $result['to']);
        self::assertEquals('RUB', $result['base']);
    }
}
