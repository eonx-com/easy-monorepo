<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Response;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseDataInterface;
use EonX\EasyErrorHandler\Interfaces\ErrorResponseFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ErrorResponseFactory implements ErrorResponseFactoryInterface
{
    public function create(Request $request, ErrorResponseDataInterface $data): Response
    {
        // TODO - Support more formats, using symfony serializer
        return new JsonResponse($data->getRawData(), $data->getStatusCode(), $data->getHeaders());
    }
}
