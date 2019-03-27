<?php
declare(strict_types=1);

namespace StepTheFkUp\ApiToken\Traits;

use EoneoPay\Utils\Str;
use Psr\Http\Message\ServerRequestInterface;

trait ApiTokenDecoderTrait
{
    /**
     * Get first value for given header and request, return null if not found.
     *
     * @param string $header
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|string
     */
    private function getFirstHeaderValue(string $header, ServerRequestInterface $request): ?string
    {
        return $request->getHeader(\strtolower($header))[0] ?? null;
    }

    /**
     * Get given header's value and remove given prefix, return null if header not found or doesn't start with prefix.
     *
     * @param string $header
     * @param string $prefix
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|string
     */
    private function getHeaderWithoutPrefix(string $header, string $prefix, ServerRequestInterface $request): ?string
    {
        if ($this->headerStartsWith($header, $prefix, $request) === false) {
            return null;
        }

        return \substr((string)$this->getFirstHeaderValue($header, $request), \strlen($prefix));
    }

    /**
     * Get given query param from given request.
     *
     * @param string $param
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return null|mixed
     */
    private function getQueryParam(string $param, ServerRequestInterface $request)
    {
        return $request->getQueryParams()[$param] ?? null;
    }

    /**
     * Check if given header starts with given prefix.
     *
     * @param string $header
     * @param string $prefix
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    private function headerStartsWith(string $header, string $prefix, ServerRequestInterface $request): bool
    {
        return (new Str())->startsWith($this->getFirstHeaderValue($header, $request) ?? '', $prefix);
    }
}
