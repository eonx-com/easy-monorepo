<?php
declare(strict_types=1);

namespace Tests\App\Unit\Services\Identity\Auth0;

use StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException;
use StepTheFkUp\EasyIdentity\Implementations\Auth0\Config;
use StepTheFkUp\EasyIdentity\Tests\AbstractTestCase;

/**
 * @covers \StepTheFkUp\EasyIdentity\Implementations\Auth0\Config
 */
class ConfigTest extends AbstractTestCase
{
    /**
     * Config should return the values passed via the constructor.
     *
     * @return void
     *
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
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
     * @throws \StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException
     */
    public function testRequiredDataMissingException(): void
    {
        $this->expectException(RequiredDataMissingException::class);

        (new Config())->getClientId();
    }
}
