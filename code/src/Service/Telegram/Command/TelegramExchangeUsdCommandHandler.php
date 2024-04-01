<?php

declare(strict_types=1);

namespace App\Service\Telegram\Command;

use App\RequestDto\CurrencyExchangeRateActionRequestDto;
use App\RequestDto\Telegram\TelegramMessageEntityTypeEnum;
use App\RequestDto\Telegram\TelegramUpdateDto;
use App\Service\CurrencyService;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

// somehow find to auto-inject it to tg webhook handler
#[AutoconfigureTag('app.telegram_exchange_command_handler')]
readonly class TelegramExchangeUsdCommandHandler
{
    public const COMMAND = '#\/exchange_(?<currency>(usd|rub|thb))$#';

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
        if (preg_match(self::COMMAND, $command) !== 1) {
            return false;
        }

        return true;
    }

    public function handle(TelegramUpdateDto $updateDto): ?string
    {
        if (!$this->supports($updateDto)) {
            return null;
        }

        $requestDto = new CurrencyExchangeRateActionRequestDto();

        $matches = [];
        preg_match(self::COMMAND, $updateDto->message->text, $matches);

        $requestDto->base = strtoupper($matches['currency']);
        $requestDto->to   = array_diff(['RUB', 'THB', 'USD'], [$requestDto->base]);

        $rateResponse = $this->currencyService->getRate($requestDto);

        $result = "Курс для {$requestDto->base}:\n";

        foreach ($rateResponse->rates as $currency => $rate) {
            $result .= sprintf("1 %s = %.4F %s\n", $rateResponse->base, $rate, $currency);
        }

        $result .= sprintf("Данные на момент: %s\n", $rateResponse->date->format('Y-m-d H:i:s'));

        return $result;
    }
}
