<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\ClientInterface;
use EonX\EasyIdentity\Exceptions\RequiredDataMissingException;

class ManagementTokenProvider
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var \EonX\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * ManagementTokenProvider constructor.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param \EonX\EasyIdentity\Implementations\Auth0\Config $config
     */
    public function __construct(ClientInterface $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Get token.
     *
     * @return string
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken(): string
    {
        $response = $this->client->request('POST', '/oauth/token', [
            'json' => [
                'audience' => \sprintf('https://%s/api/v2/', $this->config->getDomain()),
                'client_id' => $this->config->getClientId(),
                'client_secret' => $this->config->getClientSecret(),
                'grant_type' => 'client_credentials'
            ]
        ]);

        $data = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        $accessToken = $data['access_token'] ?? null;
        if (\is_string($accessToken) === true && \trim($accessToken) !== '') {
            return \trim($accessToken);
        }

        throw new RequiredDataMissingException('Required "access_token" missing for Auth0');
    }
}


