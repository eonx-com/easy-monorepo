<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Config;

use EonX\EasyNotification\Config\ConfigFinder;
use EonX\EasyNotification\Tests\AbstractTestCase;
use Nette\Utils\Json;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class ConfigFinderTest extends AbstractTestCase
{
    public function testHydrateConfigFromHttpResponse(): void
    {
        $defaultConfig = static::$defaultConfig;
        $responses = [new MockResponse(Json::encode($defaultConfig))];
        $httpClient = new MockHttpClient($responses);

        $configFinder = new ConfigFinder('https://api.com', $httpClient);
        $config = $configFinder->find('my-api-key', $defaultConfig['externalId']);

        self::assertEquals($defaultConfig['algorithm'], $config->getAlgorithm());
        self::assertEquals($defaultConfig['externalId'], $config->getProviderExternalId());
        self::assertEquals($defaultConfig['queueRegion'], $config->getQueueRegion());
        self::assertEquals($defaultConfig['queueUrl'], $config->getQueueUrl());
        self::assertEquals($defaultConfig['secret'], $config->getSecret());
    }
}
