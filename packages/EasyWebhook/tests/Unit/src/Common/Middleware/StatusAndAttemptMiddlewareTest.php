<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Entity\WebhookResultInterface;
use EonX\EasyWebhook\Common\Enum\WebhookOption;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Exception\WebhookHttpClientOptionNotAllowedException;
use EonX\EasyWebhook\Common\Exception\WebhookRequestFailedException;
use EonX\EasyWebhook\Common\Exception\WebhookResponseTooLargeException;
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

        yield 'failed with attempts left when HTTP client option is not allowed' => [
            new WebhookResult(Webhook::fromArray([
                WebhookOption::MaxAttempt->value => 2,
            ]), null, new WebhookHttpClientOptionNotAllowedException('not allowed')),
            WebhookStatus::Failed,
        ];

        yield 'failed with attempts left when response is too large' => [
            new WebhookResult(Webhook::fromArray([
                WebhookOption::MaxAttempt->value => 2,
            ]), null, new WebhookRequestFailedException(
                message: 'failed',
                previous: new WebhookResponseTooLargeException('too large')
            )),
            WebhookStatus::Failed,
        ];

        yield 'failed pending retry when request failed' => [
            new WebhookResult(Webhook::fromArray([
                WebhookOption::MaxAttempt->value => 2,
            ]), null, new WebhookRequestFailedException('failed')),
            WebhookStatus::FailedPendingRetry,
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
