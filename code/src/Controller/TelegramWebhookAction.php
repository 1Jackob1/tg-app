<?php

declare(strict_types=1);

namespace App\Controller;

use App\RequestDto\Telegram\TelegramUpdateDto;
use App\Service\Telegram\TelegramWebhookHandler;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[AsController]
class TelegramWebhookAction extends AbstractController
{
    #[Route(
        path: '/telegram-app/webhook',
        methods: [Request::METHOD_GET, Request::METHOD_POST],
    )]
    public function __invoke(
        #[MapRequestPayload]
        TelegramUpdateDto $requestDto,
        TelegramWebhookHandler $handler,
        LoggerInterface $logger,
    ): JsonResponse {
        try {
            $handler->handle($requestDto);
        } catch (Throwable $e) {
            $logger->error('[TG_ERROR_01]Unable to handle tg webhook: ' . $e->getMessage(), [
                'error_class' => $e::class,
                'error_trace' => $e->getTrace(),
            ]);
        }

        return new JsonResponse([]);
    }
}
