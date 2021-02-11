<?php

declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use DateTime;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Middleware\SendAfterMiddleware;
use EonX\EasyWebhook\Stores\ArrayStore;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;

final class SendAfterMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function provideTestProcess(): iterable
    {
        yield 'No send after -> should send' => [Webhook::fromArray([]), true];

        yield 'Send after passed -> should send' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_SEND_AFTER => (new DateTime())->modify('-1 day'),
            ]),
            true,
        ];

        yield 'Send after in future -> should not send' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_SEND_AFTER => (new DateTime())->modify('+1 day'),
            ]),
            false,
        ];
    }

    /**
     * @dataProvider provideTestProcess
     */
    public function testProcess(WebhookInterface $webhook, bool $shouldSend): void
    {
        $store = new ArrayStore($this->getRandomGenerator());
        $middleware = new SendAfterMiddleware($store);

        $this->process($middleware, $webhook);

        self::assertCount($shouldSend ? 0 : 1, $store->getWebhooks());
    }
}
