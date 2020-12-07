<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Config;

use EonX\EasyNotification\Helpers\StringHelper;
use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ConfigFinder implements ConfigFinderInterface
{
    /**
     * @var string
     */
    private $apiUrl;

    /**
     * @var \Symfony\Contracts\HttpClient\HttpClientInterface
     */
    private $httpClient;

    public function __construct(string $apiUrl, ?HttpClientInterface $httpClient = null)
    {
        $this->apiUrl = $apiUrl;
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function find(string $apiKey, string $providerExternalId): ConfigInterface
    {
        $options = [
            'auth_basic' => [$apiKey],
            'headers' => [
                'Accept' => 'application/json',
            ],
        ];

        $url = \sprintf('%sproviders/%s', StringHelper::ensureEnd($this->apiUrl, '/'), $providerExternalId);
        $response = $this->httpClient->request('GET', $url, $options)
            ->toArray();

        return Config::fromArray($response + [
            'apiKey' => $apiKey,
            'apiUrl' => $this->apiUrl,
        ]);
    }
}
