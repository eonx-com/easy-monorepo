<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Decoder;

use Symfony\Component\HttpFoundation\Request;

abstract class AbstractDecoder implements DecoderInterface
{
    public function __construct(
        private ?string $name = null,
    ) {
        // No body needed
    }

    public function getName(): string
    {
        return $this->name ?? static::class;
    }

    protected function getHeaderWithoutPrefix(string $header, string $prefix, Request $request): ?string
    {
        $header = (string)$request->headers->get($header, '');

        if (\str_starts_with($header, $prefix) === false) {
            return null;
        }

        return \substr($header, \strlen($prefix));
    }

    protected function getQueryParam(string $param, Request $request): mixed
    {
        return $request->query->get($param);
    }
}
