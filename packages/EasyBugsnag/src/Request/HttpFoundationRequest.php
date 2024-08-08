<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Request;

use Bugsnag\Request\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

final readonly class HttpFoundationRequest implements RequestInterface
{
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
        /** @var string $content */
        $content = $this->request->getContent();

        if (\str_contains((string)($this->request->getContentTypeFormat() ?? ''), 'json')) {
            $content = \json_decode($content) ?? $content;
        }

        $request = [
            'client_ip' => $this->request->getClientIp(),
            'content' => $content,
            'headers' => $this->formatHeaders($this->request),
            'method' => $this->request->getMethod(),
            'query' => $this->request->query->all(),
            'url' => $this->request->getUri(),
        ];

        if (\count($this->request->request->all()) > 0) {
            $request['request'] = $this->request->request->all();
        }

        return [
            'request' => $request,
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

        return $this->request->getSession()
            ->all();
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
}
