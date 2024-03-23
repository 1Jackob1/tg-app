<?php

declare(strict_types=1);

namespace App\Controller;

use App\Client\CurrencyBean\CurrencyBeaconConnector;
use App\Client\CurrencyBean\Request\CurrencyBeaconLatestRequest;
use App\RequestDto\CurrencyExchangeRateActionRequestDto;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class CurrencyExchangeRateAction extends AbstractController
{
    #[Route(
        path: '/currency/exchange-rate',
        methods: [Request::METHOD_GET],
    )]
    public function __invoke(
        #[MapQueryString]
        CurrencyExchangeRateActionRequestDto $requestDto,
        CurrencyBeaconConnector $connector,
    ): Response {
        $request = new CurrencyBeaconLatestRequest(
            $requestDto->base,
            $requestDto->to,
            $connector,
        );

        $result = $request->execute();

        return new JsonResponse([
            'base' => $result->base,
            'to'   => $result->rates,
            'date' => $result->date->toISOString(),
        ]);
    }
}
