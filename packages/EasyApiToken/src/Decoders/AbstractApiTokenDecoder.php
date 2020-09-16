<?php

declare(strict_types=1);

namespace EonX\EasyApiToken\Decoders;

use EonX\EasyApiToken\Interfaces\ApiTokenDecoderInterface;
use Nette\Utils\Strings;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractApiTokenDecoder implements ApiTokenDecoderInterface
{
    /**
     * @var null|string
     */
    private $name;

    public function __construct(?string $name = null)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name ?? self::class;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request|\Psr\Http\Message\ServerRequestInterface $request
     */
    protected function getHeaderWithoutPrefix(string $header, string $prefix, $request): ?string
    {
        if ($request instanceof ServerRequestInterface) {
            @\trigger_error(\sprintf(
                'Passing $request as %s is deprecated since 2.4 and removed in 3.0. Use %s instead.',
                ServerRequestInterface::class,
                Request::class
            ), \E_USER_DEPRECATED);

            return $this->getHeaderWithoutPrefixForServerRequest($header, $prefix, $request);
        }

        $header = $request->headers->get($header, '');

        if (Strings::startsWith($header ?? '', $prefix) === false) {
            return null;
        }

        return \substr((string)$header, \strlen($prefix));
    }

    /**
     * @return null|mixed
     */
    protected function getQueryParam(string $param, ServerRequestInterface $request)
    {
        @\trigger_error(\sprintf(
            'Passing $request as %s is deprecated since 2.4 and removed in 3.0. Use %s instead.',
            ServerRequestInterface::class,
            Request::class
        ), \E_USER_DEPRECATED);

        return $request->getQueryParams()[$param] ?? null;
    }

    private function getFirstHeaderValue(string $header, ServerRequestInterface $request): ?string
    {
        return $request->getHeader(\strtolower($header))[0] ?? null;
    }

    private function getHeaderWithoutPrefixForServerRequest(
        string $header,
        string $prefix,
        ServerRequestInterface $request
    ): ?string {
        if ($this->headerStartsWith($header, $prefix, $request) === false) {
            return null;
        }

        return \substr((string)$this->getFirstHeaderValue($header, $request), \strlen($prefix));
    }

    private function headerStartsWith(string $header, string $prefix, ServerRequestInterface $request): bool
    {
        return Strings::startsWith($this->getFirstHeaderValue($header, $request) ?? '', $prefix);
    }
}
