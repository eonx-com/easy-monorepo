<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Common\Request;

use Bugsnag\Request\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class HttpFoundationRequest implements RequestInterface
{
    private const CONTENT_LENGTH_MAX = 10_240;

    private const SESSION_CSRF_KEY_PREFIX = '_csrf/';

    private const SESSION_SECURITY_KEY_PREFIX = '_security_';

    public function __construct(
        private Request $request,
    ) {
    }

    public function getContext(): string
    {
        return \sprintf('%s %s', $this->request->getMethod(), $this->request->getPathInfo());
    }

    public function getCookies(): array
    {
        return $this->request->cookies->all();
    }

    public function getMetaData(): array
    {
        $request = [
            'client_ip' => $this->request->getClientIp(),
            'headers' => $this->formatHeaders($this->request),
            'method' => $this->request->getMethod(),
            'query' => $this->request->query->all(),
            'url' => $this->request->getUri(),
        ];

        // The parsed form fields are the copy the sanitizer can mask by key, so the raw body is not sent with them
        if (\count($this->request->request->all()) > 0) {
            $request['request'] = $this->request->request->all();

            return [
                'request' => $request,
            ];
        }

        return [
            'request' => [...$request, ...$this->getContent()],
        ];
    }

    public function getSession(): array
    {
        if ($this->request->hasSession() === false) {
            return [];
        }

        if ($this->request->attributes->get('_stateless')) {
            return [];
        }

        // The serialized security token (with the user object) and CSRF token values are not useful for debugging
        return \array_filter(
            $this->request->getSession()
                ->all(),
            static fn (int|string $key): bool => \str_starts_with((string)$key, self::SESSION_CSRF_KEY_PREFIX) === false
                && \str_starts_with((string)$key, self::SESSION_SECURITY_KEY_PREFIX) === false,
            \ARRAY_FILTER_USE_KEY
        );
    }

    public function getUserId(): ?string
    {
        return $this->request->getClientIp();
    }

    public function isRequest(): bool
    {
        return true;
    }

    private function formatHeaders(Request $request): array
    {
        return \array_map(static function (array $header) {
            if (\count($header) > 1) {
                return $header;
            }

            return \reset($header);
        }, $request->headers->all());
    }

    /**
     * A JSON body is decoded, so the sanitizer can mask it by key. Any other body is sanitized only by value
     * patterns, so it is cut to the first 10 KB
     */
    private function getContent(): array
    {
        $content = $this->request->getContent();

        if (\str_contains((string)($this->request->getContentTypeFormat() ?? ''), 'json')) {
            $decoded = \json_decode($content);

            if ($decoded !== null) {
                return ['content' => $decoded];
            }
        }

        if (\strlen($content) <= self::CONTENT_LENGTH_MAX) {
            return ['content' => $content];
        }

        return [
            'content' => \substr($content, 0, self::CONTENT_LENGTH_MAX),
            'content_truncated' => true,
        ];
    }
}
