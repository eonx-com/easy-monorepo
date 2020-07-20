<?php
declare(strict_types=1);

namespace EonX\EasyWebhooks\Tests;

use EonX\EasyWebhooks\HttpClientFactory;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientFactoryTest extends AbstractTestCase
{
    public function testCreate(): void
    {
        self::assertInstanceOf(HttpClientInterface::class, (new HttpClientFactory())->create());
    }
}
