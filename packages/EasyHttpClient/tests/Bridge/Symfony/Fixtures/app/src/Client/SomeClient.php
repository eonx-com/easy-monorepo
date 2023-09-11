<?php

namespace EonX\EasyHttpClient\Tests\Bridge\Symfony\Fixtures\App\Client;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SomeClient
{
    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    public function makeRequest(): void
    {
        $this->httpClient->request(Request::METHOD_GET, 'https://eonx.com');
    }
}
