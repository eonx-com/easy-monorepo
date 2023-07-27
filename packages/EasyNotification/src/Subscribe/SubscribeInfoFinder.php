<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Subscribe;

use EonX\EasyNotification\Helpers\StringHelper;
use EonX\EasyNotification\Interfaces\SubscribeInfoFinderInterface;
use EonX\EasyNotification\Interfaces\SubscribeInfoInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class SubscribeInfoFinder implements SubscribeInfoFinderInterface
{
    private HttpClientInterface $httpClient;

    public function __construct(
        private string $apiUrl,
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
    public function find(string $apiKey, string $providerExternalId, array $topics): SubscribeInfoInterface
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
