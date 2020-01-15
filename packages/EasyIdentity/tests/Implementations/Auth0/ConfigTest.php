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
    /**
     * Config should return the values passed via the constructor.
     *
     * @return void
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testGettersReturnExpectedValues(): void
    {
        $expected = [
            'client_id' => 'client_id',
            'client_secret' => 'client_secret',
            'connection' => 'connection',
            'domain' => 'domain'
        ];

        $config = new Config($expected);

        self::assertSame($expected['client_id'], $config->getClientId());
        self::assertSame($expected['client_secret'], $config->getClientSecret());
        self::assertSame($expected['connection'], $config->getConnection());
        self::assertSame($expected['domain'], $config->getDomain());
    }

    /**
     * Config should throw an exception when required data missing.
     *
     * @return void
     *
     * @throws \EonX\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testRequiredDataMissingException(): void
    {
        $config = new Config();

        $this->expectException(RequiredDataMissingException::class);

        $config->getClientId();
    }
}
