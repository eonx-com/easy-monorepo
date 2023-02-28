<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Response;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseDataInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use EonX\EasyErrorHandler\Interfaces\FormatAwareInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ErrorResponseFactory implements ErrorResponseFactoryInterface, FormatAwareInterface
{
    private const FORMAT_JSON = 'json';

    public function create(Request $request, ErrorResponseDataInterface $data): Response
    {
        return new JsonResponse($data->getRawData(), $data->getStatusCode(), $data->getHeaders());
    }

    public function supportsFormat(Request $request): bool
    {
        return $request->getPreferredFormat() === self::FORMAT_JSON;
    }
}
