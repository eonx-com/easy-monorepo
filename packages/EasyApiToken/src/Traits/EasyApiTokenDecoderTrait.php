<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Traits;

use Nette\Utils\Strings;
use Psr\Http\Message\ServerRequestInterface;

trait EasyApiTokenDecoderTrait
{
    private function getFirstHeaderValue(string $header, ServerRequestInterface $request): ?string
    {
        return $request->getHeader(\strtolower($header))[0] ?? null;
    }

    private function getHeaderWithoutPrefix(string $header, string $prefix, ServerRequestInterface $request): ?string
    {
        if ($this->headerStartsWith($header, $prefix, $request) === false) {
            return null;
        }

        return \substr((string)$this->getFirstHeaderValue($header, $request), \strlen($prefix));
    }

    /**
     * @return null|mixed
     */
    private function getQueryParam(string $param, ServerRequestInterface $request)
    {
        return $request->getQueryParams()[$param] ?? null;
    }

    private function headerStartsWith(string $header, string $prefix, ServerRequestInterface $request): bool
    {
        return Strings::startsWith($this->getFirstHeaderValue($header, $request) ?? '', $prefix);
    }
}
