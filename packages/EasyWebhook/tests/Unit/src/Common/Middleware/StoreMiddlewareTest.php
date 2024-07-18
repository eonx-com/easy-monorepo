<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\ShouldNotBeStoredWebhookResult;
use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Middleware\StoreMiddleware;
use EonX\EasyWebhook\Common\Store\ArrayResultStore;
use EonX\EasyWebhook\Common\Store\ArrayStore;
use PHPUnit\Framework\Attributes\DataProvider;

final class StoreMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'Should store' => [1];

        yield 'Should not store' => [
            0,
            new ShouldNotBeStoredWebhookResult(Webhook::create('https://eonx.com')),
        ];
    }

    #[DataProvider('provideProcessData')]
    public function testProcess(int $resultsCount, ?WebhookResultInterface $webhookResult = null): void
    {
        $webhook = new Webhook();
        $store = new ArrayStore(self::getRandomGenerator(), $this->getDataCleaner());
        $resultStore = new ArrayResultStore(self::getRandomGenerator(), $this->getDataCleaner());
        $middleware = new StoreMiddleware($store, $resultStore);

        $result = $this->process($middleware, $webhook, $webhookResult);
        $webhooks = $store->getWebhooks();
        $results = $resultStore->getResults();
        $firstWebhook = \reset($webhooks);
        $firstResult = \reset($results);

        self::assertCount($resultsCount, $webhooks);
        self::assertCount($resultsCount, $results);

        if ($resultsCount > 0) {
            self::assertSame($firstWebhook, $webhook);
            self::assertSame($firstResult, $result);
        }
    }
}
