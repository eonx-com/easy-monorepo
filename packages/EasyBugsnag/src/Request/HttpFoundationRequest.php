<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Request;

use Bugsnag\Request\RequestInterface;
use Nette\Utils\Strings;
use Symfony\Component\HttpFoundation\Request;

final class HttpFoundationRequest implements RequestInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function getContext(): string
    {
        return \sprintf('%s %s', $this->request->getMethod(), $this->request->getPathInfo());
    }

    /**
     * @return mixed[]
     */
    public function getCookies(): array
    {
        return $this->request->cookies->all();
    }

    /**
     * @return mixed[]
     */
    public function getMetaData(): array
    {
        /** @var string $content */
        $content = $this->request->getContent();

        if (Strings::contains((string)($this->request->getContentType() ?? ''), 'json')) {
            $content = \json_decode($content) ?? $content;
        }

        $request = [
            'client_ip' => $this->request->getClientIp(),
            'url' => $this->request->getUri(),
            'method' => $this->request->getMethod(),
            'headers' => $this->formatHeaders($this->request),
            'query' => $this->request->query->all(),
            'content' => $content,
        ];

        if (\count($this->request->request->all()) > 0) {
            $request['request'] = $this->request->request->all();
        }

        return [
            'request' => $request,
        ];
    }

    /**
     * @return mixed[]
     */
    public function getSession(): array
    {
        if ($this->request->hasSession() === false) {
            return [];
        }

        if ($this->request->attributes->get('_stateless') === true) {
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

    /**
     * @return mixed[]
     */
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
