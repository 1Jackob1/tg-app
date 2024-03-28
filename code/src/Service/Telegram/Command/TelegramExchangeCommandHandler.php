<?php

namespace App\Service\Telegram\Command;

use App\RequestDto\CurrencyExchangeRateActionRequestDto;
use App\RequestDto\Telegram\TelegramMessageEntityDto;
use App\RequestDto\Telegram\TelegramMessageEntityTypeEnum;
use App\RequestDto\Telegram\TelegramUpdateDto;
use App\Service\CurrencyService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

// somehow find to auto-inject it to tg webhook handler
#[AutoconfigureTag('app.telegram_exchange_command_handler')]
readonly class TelegramExchangeCommandHandler
{
    public const COMMAND = '/exchange';

    public function __construct(
        private CurrencyService $currencyService,
    ) {}

    public function supports(TelegramUpdateDto $updateDto): bool
    {
        $messageDto = $updateDto->message;
        if (count($messageDto->entities) !== 1) {
            return false;
        }

        $messageEntityDto = $messageDto->entities[0];
        if ($messageEntityDto->type !== TelegramMessageEntityTypeEnum::BOT_COMMAND) {
            return false;
        }

        $command = mb_substr($messageDto->text, $messageEntityDto->offset, $messageEntityDto->length);
        if ($command !== self::COMMAND) {
            return false;
        }

        return true;
    }

    public function handle(TelegramUpdateDto $updateDto): ?string
    {
        if (!$this->supports($updateDto)) {
            return null;
        }

        $parts = explode(' ', $updateDto->message->text);

        $requestDto = new CurrencyExchangeRateActionRequestDto();
        $base = $parts[1];
        $to = $parts[2];

        $requestDto->base = $base;
        $requestDto->to = [$to];

        $rate = $this->currencyService->getRate($requestDto);

        return sprintf(
            "Курс обмена между %s и %s равен:\n1 %s = %.4F %s\nДанные на момент: %s",
            $base,
            $to,
            $base,
            $rate->rates[$to],
            $to,
            $rate->date->format('Y-m-d H:i:s')
        );
    }
}
