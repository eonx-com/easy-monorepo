<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Unit\Provider;

use EonX\EasyNotification\Provider\ConfigProvider;
use EonX\EasyNotification\Tests\Unit\AbstractUnitTestCase;
use Nette\Utils\Json;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ConfigProviderTest extends AbstractUnitTestCase
{
    public function testHydrateConfigFromHttpResponse(): void
    {
        $defaultConfig = static::$defaultConfig;
        $responses = [new MockResponse(Json::encode($defaultConfig))];
        $httpClient = new MockHttpClient($responses);

        $configFinder = new ConfigProvider('https://api.com', $httpClient);
        $config = $configFinder->provide('my-api-key', $defaultConfig['externalId']);

        self::assertEquals($defaultConfig['algorithm'], $config->getAlgorithm());
        self::assertEquals($defaultConfig['externalId'], $config->getProviderExternalId());
        self::assertEquals($defaultConfig['queueRegion'], $config->getQueueRegion());
        self::assertEquals($defaultConfig['queueUrl'], $config->getQueueUrl());
        self::assertEquals($defaultConfig['secret'], $config->getSecret());
    }
}
