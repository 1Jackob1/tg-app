<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Client\CurrencyBean\CurrencyBeaconConnector;
use App\Tests\AppTestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class CurrencyExchangeRateActionTest extends AppTestCase
{
    public function testAction(): void
    {
        $mockClient = new MockClient([
            MockResponse::make(self::getStub('currencybeacon_rub_to_aed_thb_success_response.json')),
        ]);

        $connector = self::getObjectFromContainer(CurrencyBeaconConnector::class);
        $connector->withMockClient($mockClient);

        $this->client->request('GET', '/currency/exchange-rate', ['base' => 'RUB', 'to' => ['AED', 'THB']]);
        $responseContent = $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertNotFalse($responseContent);
        self::assertJson($responseContent);

        $decodedData      = json_decode($responseContent, true);
        $propertyAccessor = self::getObjectFromContainer(PropertyAccessorInterface::class);

        self::assertEquals(0.0401, $propertyAccessor->getValue($decodedData, '[to][AED]'));
        self::assertEquals(0.3895, $propertyAccessor->isReadable($decodedData, '[to][THB]'));
        self::assertEquals('RUB', $propertyAccessor->isReadable($decodedData, '[base]'));
    }
}
