<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Factory;

use EonX\EasyErrorHandler\Common\ErrorHandler\FormatAwareInterface;
use EonX\EasyErrorHandler\Common\ValueObject\ErrorResponseData;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ErrorResponseFactory implements ErrorResponseFactoryInterface, FormatAwareInterface
{
    private const string FORMAT_JSON = 'json';

    public function create(Request $request, ErrorResponseData $data): Response
    {
        return new JsonResponse($data->getRawData(), $data->getStatusCode()->value, $data->getHeaders());
    }

    public function supportsFormat(Request $request): bool
    {
        return $request->getPreferredFormat() === self::FORMAT_JSON;
    }
}
