<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use DateTime;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Enum\WebhookOption;
use EonX\EasyWebhook\Common\Middleware\SendAfterMiddleware;
use EonX\EasyWebhook\Common\Store\ArrayStore;
use PHPUnit\Framework\Attributes\DataProvider;

final class SendAfterMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'No send after -> should send' => [Webhook::fromArray([]), true];

        yield 'Send after passed -> should send' => [
            Webhook::fromArray([
                WebhookOption::SendAfter->value => new DateTime()->modify('-1 day'),
            ]),
            true,
        ];

        yield 'Send after in future -> should not send' => [
            Webhook::fromArray([
                WebhookOption::SendAfter->value => new DateTime()->modify('+1 day'),
            ]),
            false,
        ];
    }

    #[DataProvider('provideProcessData')]
    public function testProcess(WebhookInterface $webhook, bool $shouldSend): void
    {
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
        $middleware = new SendAfterMiddleware($store);

        $this->process($middleware, $webhook);

        self::assertCount($shouldSend ? 0 : 1, $store->getWebhooks());
    }
}
