<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Tests\Implementations\Auth0;

use EonX\EasyIdentity\Exceptions\RequiredDataMissingException;
use EonX\EasyIdentity\Implementations\Auth0\Config;
use EonX\EasyIdentity\Tests\AbstractTestCase;

/**
 * @covers \EonX\EasyIdentity\Implementations\Auth0\Config
 */
class ConfigTest extends AbstractTestCase
{
    public function testGettersReturnExpectedValues(): void
    {
        $expected = [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain',
        ];

        $config = new Config($expected);

        self::assertEquals($expected['client_id'], $config->getClientId());
        self::assertEquals($expected['client_secret'], $config->getClientSecret());
        self::assertEquals($expected['connection'], $config->getConnection());
        self::assertEquals($expected['domain'], $config->getDomain());
    }

    public function testRequiredDataMissingException(): void
    {
        $config = new Config();

        $this->expectException(RequiredDataMissingException::class);

        $config->getClientId();
    }
}
