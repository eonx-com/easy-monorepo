<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Implementations\Auth0;

use GuzzleHttp\ClientInterface;
use StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException;

class ManagementTokenProvider
{
    /**
     * @var \GuzzleHttp\ClientInterface
     */
    private $client;

    /**
     * @var \StepTheFkUp\EasyIdentity\Implementations\Auth0\Config
     */
    private $config;

    /**
     * ManagementTokenProvider constructor.
     *
     * @param \GuzzleHttp\ClientInterface $client
     * @param \StepTheFkUp\EasyIdentity\Implementations\Auth0\Config $config
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
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
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

        if (empty($data['access_token'] ?? null) === false) {
            return $data['access_token'];
        }

        throw new RequiredDataMissingException('Required "access_token" missing for Auth0');
    }
}

\class_alias(
    ManagementTokenProvider::class,
    'LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementTokenProvider',
    false
);
