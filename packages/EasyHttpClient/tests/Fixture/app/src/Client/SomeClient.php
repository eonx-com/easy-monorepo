<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Fixture\App\Client;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final readonly class SomeClient
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {}

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function makeRequest(): ResponseInterface
    {
        return $this->httpClient->request(Request::METHOD_GET, 'https://eonx.com');
    }
}
