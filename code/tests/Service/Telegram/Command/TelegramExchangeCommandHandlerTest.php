<?php

declare(strict_types=1);

namespace App\Tests\Service\Telegram\Command;

use App\Client\CurrencyBeacon\CurrencyBeaconConnector;
use App\Client\CurrencyBeacon\Request\CurrencyBeaconLatestRequest;
use App\RequestDto\Telegram\TelegramMessageDto;
use App\RequestDto\Telegram\TelegramMessageEntityDto;
use App\RequestDto\Telegram\TelegramMessageEntityTypeEnum;
use App\RequestDto\Telegram\TelegramUpdateDto;
use App\Service\Telegram\Command\TelegramExchangeCommandHandler;
use App\Tests\AppTestCase;
use Carbon\CarbonImmutable;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

class TelegramExchangeCommandHandlerTest extends AppTestCase
{
    private TelegramExchangeCommandHandler $commandHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commandHandler = self::getObjectFromContainer(TelegramExchangeCommandHandler::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->commandHandler);
    }

    public function testHandle()
    {
        $mockClient = new MockClient([
            CurrencyBeaconLatestRequest::class => MockResponse::make(self::getStub('currencybeacon_rub_to_aed_thb_success_response.json')),
        ]);

        $now = CarbonImmutable::now();
        CarbonImmutable::setTestNow($now);

        $connector = self::getObjectFromContainer(CurrencyBeaconConnector::class);
        $connector->withMockClient($mockClient);
        $tgUpdateDto = $this->getTelegramUpdateDto();
        $result      = $this->commandHandler->handle($tgUpdateDto);

        self::assertEquals("Курс для RUB:\n1 RUB = 0.3895 THB\n1 RUB = 0.0000 USD\nДанные на момент: {$now->format('Y-m-d H:i:s')}\n", $result);

        CarbonImmutable::setTestNow(null);
    }

    public function testSupportsSuccess(): void
    {
        $tgUpdateDto = $this->getTelegramUpdateDto();

        $result = $this->commandHandler->supports($tgUpdateDto);

        self::assertTrue($result);
    }

    public function testSupportsAnotherCommand(): void
    {
        $tgUpdateDto = $this->getTelegramUpdateDto('/UNKNOWN_COMMAND_123');

        $result = $this->commandHandler->supports($tgUpdateDto);

        self::assertFalse($result);
    }

    public function testSupportsTypeNotBotCommand(): void
    {
        $tgUpdateDto = $this->getTelegramUpdateDto('/UNKNOWN_COMMAND_123');

        $tgUpdateDto->message->entities[0]->type = TelegramMessageEntityTypeEnum::BLOCKQUOTE;

        $result = $this->commandHandler->supports($tgUpdateDto);

        self::assertFalse($result);
    }

    public function testSupportsFailManyEntities(): void
    {
        $telegramMessageEntityDto = new TelegramMessageEntityDto();
        $telegramMessageDto       = new TelegramMessageDto();

        $telegramMessageDto->entities[] = $telegramMessageEntityDto;
        $telegramMessageDto->entities[] = $telegramMessageEntityDto;

        $tgUpdateDto          = new TelegramUpdateDto();
        $tgUpdateDto->message = $telegramMessageDto;

        $result = $this->commandHandler->supports($tgUpdateDto);

        self::assertFalse($result);
    }

    private function getTelegramUpdateDto(string $text = '/exchange_rub'): TelegramUpdateDto
    {
        $telegramMessageEntityDto = new TelegramMessageEntityDto();
        $telegramMessageDto       = new TelegramMessageDto();

        $telegramMessageDto->text         = $text;
        $telegramMessageDto->entities[]   = $telegramMessageEntityDto;
        $telegramMessageEntityDto->offset = 0;
        $telegramMessageEntityDto->type   = TelegramMessageEntityTypeEnum::BOT_COMMAND;
        $telegramMessageEntityDto->length = mb_strlen($telegramMessageDto->text);

        $tgUpdateDto          = new TelegramUpdateDto();
        $tgUpdateDto->message = $telegramMessageDto;

        return $tgUpdateDto;
    }
}
