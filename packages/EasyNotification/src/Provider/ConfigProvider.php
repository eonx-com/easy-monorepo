<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Provider;

use EonX\EasyNotification\Helper\StringHelper;
use EonX\EasyNotification\ValueObject\Config;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ConfigProvider implements ConfigProviderInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(
        private string $apiUrl,
        ?HttpClientInterface $httpClient = null,
    ) {
        $this->httpClient = $httpClient ?? HttpClient::create();
    }

    public function provide(string $apiKey, string $providerExternalId): Config
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

        return Config::fromArray(\array_merge([
            'apiKey' => $apiKey,
            'apiUrl' => $this->apiUrl,
        ], $response));
    }
}
