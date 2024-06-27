<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Factory;

use EonX\EasyErrorHandler\Common\ValueObject\ErrorResponseDataInterface;
use EonX\EasyErrorHandler\Common\ValueObject\ErrorResponseFormat;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

final class SymfonySerializerErrorResponseFactory implements ErrorResponseFactoryInterface
{
    private readonly array $errorFormats;

    public function __construct(
        private readonly SerializerInterface $serializer,
        ?array $errorFormats = null,
    ) {
        $this->errorFormats = $errorFormats ?? [];
    }

    public function create(Request $request, ErrorResponseDataInterface $data): Response
    {
        $format = $this->getFormat($request);

        $headers = $data->getHeaders();
        $headers['Content-Type'] = \sprintf('%s; charset=utf-8', $format->getValue());
        $headers['X-Content-Type-Options'] = 'nosniff';
        $headers['X-Frame-Options'] = 'deny';

        $statusCode = $data->getStatusCode();

        $content = $this->serializer->serialize(
            $data->getRawData(),
            $format->getKey(),
            ['statusCode' => $statusCode]
        );

        return new Response($content, $statusCode, $headers);
    }

    private function getFormat(Request $request): ErrorResponseFormat
    {
        $requestFormat = (string)$request->getRequestFormat('');

        if ($requestFormat !== '' && isset($this->errorFormats[$requestFormat])) {
            return ErrorResponseFormat::create($requestFormat, $this->errorFormats[$requestFormat][0]);
        }

        $requestMimeTypes = Request::getMimeTypes($requestFormat);
        $errorFormat = null;

        foreach ($this->errorFormats as $format => $errorMimeTypes) {
            if ($errorFormat === null || \array_intersect($requestMimeTypes, $errorMimeTypes)) {
                $errorFormat = ErrorResponseFormat::create($format, $errorMimeTypes[0]);
            }
        }

        return $errorFormat ?? ErrorResponseFormat::create('json', 'application/json');
    }
}
