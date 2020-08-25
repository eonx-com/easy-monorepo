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
        $content = $this->request->getContent();

        if (Strings::contains($this->request->getContentType(), 'json')) {
            $content = \json_decode($content) ?? $content;
        }

        return [
            'url' => $this->request->getUri(),
            'method' => $this->request->getMethod(),
            'headers' => $this->request->headers->all(),
            'query' => $this->request->query->all(),
            'request' => $this->request->request->all(),
            'content' => $content,
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

        return $this->request->getSession()->all();
    }

    public function getUserId(): ?string
    {
        return $this->request->getClientIp();
    }

    public function isRequest(): bool
    {
        return true;
    }
}
