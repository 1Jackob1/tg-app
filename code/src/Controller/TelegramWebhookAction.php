<?php

namespace App\Controller;

use App\Client\Telegram\Request\TelegramSendMessageRequest;
use App\Client\Telegram\TelegramConnector;
use App\RequestDto\Telegram\TelegramMessageDto;
use App\RequestDto\Telegram\TelegramUpdateDto;
use App\Service\CurrencyService;
use App\Service\Telegram\TelegramWebhookHandler;
use Saloon\Http\Connector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class TelegramWebhookAction extends AbstractController
{
    #[Route(
        path: '/telegram-app/webhook',
        methods: [Request::METHOD_GET, Request::METHOD_POST],
    )]
    public function __invoke(Request $r, #[MapRequestPayload] TelegramUpdateDto $requestDto, TelegramWebhookHandler $handler): JsonResponse
    {
        try {
            $handler->handle($requestDto);
        } catch (\Throwable $e) {
            file_put_contents(__DIR__ . '/error.json', json_encode([
                $e->getMessage(),
                $e->getTrace(),
                $requestDto,
                $r->getContent(),
            ]));
        }

        return new JsonResponse([]);
    }
}
