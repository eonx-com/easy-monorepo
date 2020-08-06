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

    public function __construct(string $apiKey, string $apiUrl, ?HttpClientInterface $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function find(): ConfigInterface
    {
        $url = \sprintf('%sme', u($this->apiUrl)->ensureEnd('/'));
        $response = $this->httpClient->request('GET', $url, ['auth_basic' => [$this->apiKey]])->toArray();

        return Config::fromArray($response);
    }
}
