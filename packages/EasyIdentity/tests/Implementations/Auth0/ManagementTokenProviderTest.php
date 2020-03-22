<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Auth0;

use EonX\EasyIdentity\Exceptions\RequiredDataMissingException;
use EonX\EasyIdentity\Implementations\Auth0\Config;
use EonX\EasyIdentity\Implementations\Auth0\ManagementTokenProvider;
use EonX\EasyIdentity\Tests\AbstractTestCase;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery\ExpectationInterface;
use Mockery\MockInterface;

/**
 * @covers \EonX\EasyIdentity\Implementations\Auth0\ManagementTokenProvider
 */
class ManagementTokenProviderTest extends AbstractTestCase
{
    public function testGetToken(): void
    {
        $config = $this->getConfig();

        /** @var \GuzzleHttp\ClientInterface $client */
        $client = $this->mock(ClientInterface::class, function (MockInterface $mock) use ($config): void {
            $this->buildBaseExpectation($mock, $config, ['access_token' => 'access_token']);
        });

        $provider = new ManagementTokenProvider($client, $config);
        $token = $provider->getToken();

        self::assertEquals('access_token', $token);
    }

    public function testGetTokenWithMissingTokenInResponse(): void
    {
        $this->expectException(RequiredDataMissingException::class);

        $config = $this->getConfig();

        /** @var \GuzzleHttp\ClientInterface $client */
        $client = $this->mock(ClientInterface::class, function (MockInterface $mock) use ($config): void {
            $this->buildBaseExpectation($mock, $config);
        });

        $provider = new ManagementTokenProvider($client, $config);
        $token = $provider->getToken();

        self::assertEquals('access_token', $token);
    }

    /**
     * @param null|mixed[] $content
     */
    private function buildBaseExpectation(
        MockInterface $mock,
        Config $config,
        ?array $content = null
    ): ExpectationInterface {
        $response = new Response(200, [], (string)\json_encode($content ?? []));

        return $mock
            ->shouldReceive('request')
            ->once()
            ->with('POST', '/oauth/token', [
                'json' => [
                    'audience' => \sprintf('https://%s/api/v2/', $config->getDomain()),
                    'client_id' => $config->getClientId(),
                    'client_secret' => $config->getClientSecret(),
                    'grant_type' => 'client_credentials',
                ],
            ])
            ->andReturn($response);
    }

    private function getConfig(): Config
    {
        return new Config([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain',
        ]);
    }
}
