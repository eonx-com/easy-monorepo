<?php

declare(strict_types=1);

namespace EonX\EasyNotification\Config;

use EonX\EasyNotification\Interfaces\ConfigFinderInterface;
use EonX\EasyNotification\Interfaces\ConfigInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\String\u;

final class ConfigFinder implements ConfigFinderInterface
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

    /**
     * @var string
     */
    private $providerExternalId;

    public function __construct(
        string $apiKey,
        string $apiUrl,
        string $providerExternalId,
        ?HttpClientInterface $httpClient = null
    ) {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->providerExternalId = $providerExternalId;
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function find(): ConfigInterface
    {
        $options = [
            'auth_basic' => [$this->apiKey],
            'headers' => ['Accept' => 'application/json'],
        ];

        $url = \sprintf('%sproviders/%s', u($this->apiUrl)->ensureEnd('/'), $this->providerExternalId);
        $response = $this->httpClient->request('GET', $url, $options)->toArray();

        return Config::fromArray($response);
    }
}
