<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Enum\WebhookOption;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Middleware\StatusAndAttemptMiddleware;
use Exception;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpClient\Response\MockResponse;

final class StatusAndAttemptMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'successful' => [new WebhookResult(new Webhook(), new MockResponse()), WebhookStatus::Success];

        yield 'failed pending retry' => [
            new WebhookResult(Webhook::fromArray([
                WebhookOption::MaxAttempt->value => 2,
            ]), null, new Exception()),
            WebhookStatus::FailedPendingRetry,
        ];

        yield 'failed' => [
            new WebhookResult(Webhook::fromArray([
                WebhookOption::MaxAttempt->value => 1,
            ]), null, new Exception()),
            WebhookStatus::Failed,
        ];
    }

    #[DataProvider('provideProcessData')]
    public function testProcess(WebhookResultInterface $webhookResult, WebhookStatus $status): void
    {
        $middleware = new StatusAndAttemptMiddleware();

        $result = $this->process($middleware, new Webhook(), $webhookResult);

        self::assertEquals($status, $result->getWebhook()->getStatus());
    }
}
