<?php
declare(strict_types=1);

namespace EonX\EasyNotification\Tests\Subscribe;

use EonX\EasyNotification\Subscribe\SubscribeInfoFinder;
use EonX\EasyNotification\Tests\AbstractTestCase;
use Nette\Utils\Json;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class SubscribeInfoFinderTest extends AbstractTestCase
{
    public function testFind(): void
    {
        $response = new MockResponse(Json::encode([
            'jwt' => 'my-jwt',
            'topics' => ['/nathan'],
            'url' => 'https://subscribe.com',
        ]));
        $httpClient = new MockHttpClient([$response]);
        $finder = new SubscribeInfoFinder('https://my-url.com', $httpClient);

        $subscribeInfo = $finder->find('my-api-key', 'my-provider', ['/nathan']);

        self::assertEquals('my-jwt', $subscribeInfo->getJwt());
        self::assertEquals(['/nathan'], $subscribeInfo->getTopics());
        self::assertEquals('https://subscribe.com', $subscribeInfo->getUrl());
    }
}
