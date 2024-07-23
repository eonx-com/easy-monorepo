<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Middleware\MethodMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;

final class MethodMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'default method' => [Webhook::fromArray([]), WebhookInterface::DEFAULT_METHOD];

        yield 'custom default method' => [Webhook::fromArray([]), 'PUT', 'PUT'];

        yield 'custom method on webhook' => [
            Webhook::fromArray([
                'method' => 'PATCH',
            ]),
            'PATCH',
        ];
    }

    #[DataProvider('provideProcessData')]
    public function testProcess(WebhookInterface $webhook, string $expectedMethod, ?string $defaultMethod = null): void
    {
        $middleware = new MethodMiddleware($defaultMethod);
        $result = $this->process($middleware, $webhook);

        self::assertEquals($expectedMethod, $result->getWebhook()->getMethod());
    }

    public function testProcessWithConfiguredWebhook(): void
    {
        $webhook = (new Webhook())->configured(true);
        $middleware = new MethodMiddleware(null, 10);

        $this->process($middleware, $webhook);

        self::assertNull($webhook->getMethod());
    }
}
