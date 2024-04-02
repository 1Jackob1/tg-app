<?php

declare(strict_types=1);

namespace App\Service\Telegram;

use App\Client\Telegram\Request\TelegramSendMessageRequest;
use App\Client\Telegram\TelegramConnector;
use App\RequestDto\Telegram\TelegramUpdateDto;
use App\Service\Telegram\Command\TelegramExchangeCommandHandler;

class TelegramWebhookHandler
{
    public function __construct(
        private TelegramExchangeCommandHandler $commandHandler,
        private TelegramConnector $telegramConnector,
    ) {}

    public function handle(TelegramUpdateDto $updateDto): void
    {
        if ($this->commandHandler->supports($updateDto)) {
            $result = $this->commandHandler->handle($updateDto) ?? 'Я сломался :(';
        } else {
            $result = 'Не известная команда :(';
        }

        $req = new TelegramSendMessageRequest($result, $updateDto->message->chat->id, $this->telegramConnector);
        $req->execute();
    }
}
