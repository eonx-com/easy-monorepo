<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Tests\Implementations\Auth0;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementTokenProvider;
use LoyaltyCorp\EasyIdentity\Tests\AbstractTestCase;
use Mockery\ExpectationInterface;
use Mockery\MockInterface;

/**
 * @covers \LoyaltyCorp\EasyIdentity\Implementations\Auth0\ManagementTokenProvider
 */
class ManagementTokenProviderTest extends AbstractTestCase
{
    /**
     * Provider should call Auth0 API to issue a new access token and return it.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetToken(): void
    {
        $config = $this->getConfig();

        /** @var \GuzzleHttp\ClientInterface $client */
        $client = $this->mock(ClientInterface::class, function (MockInterface $mock) use ($config): void {
            $this->buildBaseExpectation($mock, $config, ['access_token' => 'access_token']);
        });

        self::assertEquals('access_token', (new ManagementTokenProvider($client, $config))->getToken());
    }

    /**
     * Provider should call Auth0 API to issue a new access token and throws an exception if invalid response.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetTokenWithMissingTokenInResponse(): void
    {
        $this->expectException(RequiredDataMissingException::class);

        $config = $this->getConfig();

        /** @var \GuzzleHttp\ClientInterface $client */
        $client = $this->mock(ClientInterface::class, function (MockInterface $mock) use ($config): void {
            $this->buildBaseExpectation($mock, $config);
        });

        self::assertEquals('access_token', (new ManagementTokenProvider($client, $config))->getToken());
    }

    /**
     * Build the base expectation for the mock for the client.
     *
     * @param \Mockery\MockInterface $mock
     * @param \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config $config
     * @param null|mixed[] $content
     *
     * @return \Mockery\ExpectationInterface
     */
    private function buildBaseExpectation(
        MockInterface $mock,
        Config $config,
        ?array $content = null
    ): ExpectationInterface {
        return $mock
            ->shouldReceive('request')
            ->once()
            ->with('POST', '/oauth/token', [
                'json' => [
                    'audience' => \sprintf('https://%s/api/v2/', $config->getDomain()),
                    'client_id' => $config->getClientId(),
                    'client_secret' => $config->getClientSecret(),
                    'grant_type' => 'client_credentials'
                ]
            ])
            ->andReturn(new Response(200, [], (string)\json_encode($content ?? [])));
    }

    /**
     * Get config.
     *
     * @return \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
     */
    private function getConfig(): Config
    {
        return new Config([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain'
        ]);
    }
}

\class_alias(
    ManagementTokenProviderTest::class,
    'StepTheFkUp\EasyIdentity\Tests\Implementations\Auth0\ManagementTokenProviderTest',
    false
);
