<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient;

use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * @internal  Use `self::arrangeHttpResponse(...) ` instead
 */
final class SimpleTestResponse extends AbstractTestResponse
{
    public function __invoke(string $method, string $url, ?array $options = null): MockResponse
    {
        $this->checkUrl($url);

        return $this->createResponse($method, $url, $options ?? []);
    }
}
