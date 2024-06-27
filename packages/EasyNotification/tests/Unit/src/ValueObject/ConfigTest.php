<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\ValueObject;

use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyNotification\ValueObject\Config;

final class ConfigTest extends AbstractUnitTestCase
{
    public function testGetters(): void
    {
        $default = static::$defaultConfig;
        $config = Config::fromArray($default);

        self::assertEquals($default['algorithm'], $config->getAlgorithm());
        self::assertEquals($default['apiKey'], $config->getApiKey());
        self::assertEquals($default['apiUrl'], $config->getApiUrl());
        self::assertEquals($default['externalId'], $config->getProviderExternalId());
        self::assertEquals($default['queueRegion'], $config->getQueueRegion());
        self::assertEquals($default['queueUrl'], $config->getQueueUrl());
        self::assertEquals($default['secret'], $config->getSecret());
    }
}
