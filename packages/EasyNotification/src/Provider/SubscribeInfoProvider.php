<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Provider;

use EonX\EasyNotification\Helper\StringHelper;
use EonX\EasyNotification\ValueObject\SubscribeInfo;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SubscribeInfoProvider implements SubscribeInfoProviderInterface
{
    private readonly HttpClientInterface $httpClient;

    public function __construct(
        private readonly string $apiUrl,
        ?HttpClientInterface $httpClient = null,
    ) {
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
    public function provide(string $apiKey, string $providerExternalId, array $topics): SubscribeInfo
    {
        $options = [
            'auth_basic' => [$apiKey],
            'headers' => [
                'Accept' => 'application/json',
            ],
            'json' => [
                'topics' => $topics,
            ],
        ];

        $url = \sprintf(
            '%sproviders/%s/subscribe-info',
            StringHelper::ensureEnd($this->apiUrl, '/'),
            $providerExternalId
        );

        $response = $this->httpClient->request('POST', $url, $options)
            ->toArray();

        return SubscribeInfo::fromArray($response);
    }
}
