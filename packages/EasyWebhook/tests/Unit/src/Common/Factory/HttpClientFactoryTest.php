<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Factory;

use EonX\EasyWebhook\Common\Factory\HttpClientFactory;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class HttpClientFactoryTest extends AbstractUnitTestCase
{
    public function testCreate(): void
    {
        self::assertInstanceOf(HttpClientInterface::class, (new HttpClientFactory())->create());
    }
}
