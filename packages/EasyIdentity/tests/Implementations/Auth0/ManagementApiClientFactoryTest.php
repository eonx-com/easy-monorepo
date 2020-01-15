<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Auth0;

use EonX\EasyIdentity\Implementations\Auth0\Config;
use EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory;
use EonX\EasyIdentity\Implementations\Auth0\ManagementTokenProvider;
use EonX\EasyIdentity\Tests\AbstractTestCase;
use Mockery\MockInterface;

/**
 * @covers \EonX\EasyIdentity\Implementations\Auth0\ManagementApiClientFactory
 */
class ManagementApiClientFactoryTest extends AbstractTestCase
{
    /**
     * Factory should request a management token and return the expected instance of management api client.
     *
     * @return void
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testCreate(): void
    {
        /** @var \EonX\EasyIdentity\Implementations\Auth0\ManagementTokenProvider $tokenProvider */
        $tokenProvider = $this->mock(ManagementTokenProvider::class, static function (MockInterface $mock): void {
            $mock->shouldReceive('getToken')->once()->withNoArgs()->andReturn('access_token');
        });

        $config = new Config(['domain' => 'domain']);
        $factory = new ManagementApiClientFactory($config, $tokenProvider);
        $factory->create();

        // If no exception was thrown, test passes.
        $this->addToAssertionCount(1);
    }
}
