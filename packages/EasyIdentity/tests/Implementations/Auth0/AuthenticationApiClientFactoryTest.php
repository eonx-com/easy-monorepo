<?php
declare(strict_types=1);

namespace Tests\App\Unit\Services\Identity\Auth0;

use Auth0\SDK\API\Authentication;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\Config;
use StepTheFkUp\EasyIdentity\Tests\AbstractTestCase;

/**
 * @covers \StepTheFkUp\EasyIdentity\Implementations\Auth0\AuthenticationApiClientFactory
 */
class AuthenticationApiClientFactoryTest extends AbstractTestCase
{
    /**
     * Factory should return the expected authentication api client instance.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testCreate(): void
    {
        $config = new Config([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'domain' => 'domain'
        ]);

        self::assertInstanceOf(Authentication::class, (new AuthenticationApiClientFactory($config))->create());
    }
}
