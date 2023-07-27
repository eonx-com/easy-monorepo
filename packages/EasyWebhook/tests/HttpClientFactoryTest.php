<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests;

use EonX\EasyWebhook\HttpClientFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientFactoryTest extends AbstractTestCase
{
    public function testCreate(): void
    {
        self::assertInstanceOf(HttpClientInterface::class, (new HttpClientFactory())->create());
    }
}
