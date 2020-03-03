<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Auth0;

use EonX\EasyIdentity\Implementations\Auth0\Config;
use EonX\EasyIdentity\Implementations\Auth0\TokenVerifierFactory;
use EonX\EasyIdentity\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyIdentity\Implementations\Auth0\TokenVerifierFactory
 */
class TokenVerifierFactoryTest extends AbstractTestCase
{
    /**
     * Factory should create the expected token verifier instance.
     *
     * @return void
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     * @throws \Auth0\SDK\Exception\CoreException
     */
    public function testCreate(): void
    {
        $config = new Config([
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain'
        ]);

        $verifier = new TokenVerifierFactory($config);
        $verifier->create();

        // If no exception was thrown, test passes.
        $this->addToAssertionCount(1);
    }
}
