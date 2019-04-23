<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Tests\Implementations\Auth0;

use LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException;
use LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config;
use LoyaltyCorp\EasyIdentity\Tests\AbstractTestCase;

/**
 * @covers \LoyaltyCorp\EasyIdentity\Implementations\Auth0\Config
 */
class ConfigTest extends AbstractTestCase
{
    /**
     * Config should return the values passed via the constructor.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
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

        self::assertEquals($expected['client_id'], $config->getClientId());
        self::assertEquals($expected['client_secret'], $config->getClientSecret());
        self::assertEquals($expected['connection'], $config->getConnection());
        self::assertEquals($expected['domain'], $config->getDomain());
    }

    /**
     * Config should throw an exception when required data missing.
     *
     * @return void
     *
     * @throws \LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testRequiredDataMissingException(): void
    {
        $this->expectException(RequiredDataMissingException::class);

        (new Config())->getClientId();
    }
}

\class_alias(
    ConfigTest::class,
    'StepTheFkUp\EasyIdentity\Tests\Implementations\Auth0\ConfigTest',
    false
);
