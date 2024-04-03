<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Client\CurrencyBeacon\CurrencyBeaconConnector;
use App\Client\CurrencyBeacon\Request\CurrencyBeaconLatestRequest;
use App\Client\Telegram\Request\TelegramSendMessageRequest;
use App\Client\Telegram\TelegramConnector;
use App\Tests\AppTestCase;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class TelegramWebhookActionTest extends AppTestCase
{
    public function testAction()
    {
        $mockClient = new MockClient([
            CurrencyBeaconLatestRequest::class => MockResponse::make(self::getStub('currencybeacon_rub_to_aed_thb_success_response.json')),
            TelegramSendMessageRequest::class  => MockResponse::make(self::getStub('telegram_exchange_thb_command_success_response.json')),
        ]);

        $currencyConnector = self::getObjectFromContainer(CurrencyBeaconConnector::class);
        $currencyConnector->withMockClient($mockClient);
        $telegramConnector = self::getObjectFromContainer(TelegramConnector::class);
        $telegramConnector->withMockClient($mockClient);

        $requestContent = self::getStub('telegram_exchange_thb_command_webhook_request.json');
        $this->client->request('POST', '/telegram-app/webhook', server: ['CONTENT_TYPE' => 'application/json'], content: $requestContent);
        $responseContent = $this->client->getResponse()->getContent();

        self::assertResponseIsSuccessful();
        self::assertNotFalse($responseContent);
        self::assertJson($responseContent);
    }
}
