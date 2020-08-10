<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Subscribe;

use EonX\EasyNotification\Interfaces\SubscribeInfoFinderInterface;
use EonX\EasyNotification\Interfaces\SubscribeInfoInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\String\u;

final class SubscribeInfoFinder implements SubscribeInfoFinderInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    public function __construct(string $apiKey, string $apiUrl, ?HttpClientInterface $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    /**
     * @param string[] $topics
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function find(array $topics): SubscribeInfoInterface
    {
        $options = [
            'auth_basic' => [$this->apiKey],
            'headers' => ['Accept' => 'application/json'],
            'json' => ['topics' => $topics],
        ];

        $url = \sprintf('%s%s', u($this->apiUrl)->ensureEnd('/'), 'subscribe_infos');
        $response = $this->httpClient->request('POST', $url, $options)->toArray();

        return SubscribeInfo::fromArray($response);
    }
}
