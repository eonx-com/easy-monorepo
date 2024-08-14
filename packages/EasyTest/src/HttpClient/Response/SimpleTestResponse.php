<?php
declare(strict_types=1);

namespace EonX\EasyTest\HttpClient\Response;

final class SimpleTestResponse extends AbstractTestResponse
{
    protected function checkParameters(string $method, string $url, array $options): void
    {
        $this->checkUrl($url);
    }
}
