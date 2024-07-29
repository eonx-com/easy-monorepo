<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
use EonX\EasyWebhook\Common\Enum\WebhookOption;
use EonX\EasyWebhook\Common\Enum\WebhookStatus;
use EonX\EasyWebhook\Common\Exception\CannotRerunWebhookException;
use EonX\EasyWebhook\Common\Middleware\RerunMiddleware;
use PHPUnit\Framework\Attributes\DataProvider;

final class RerunMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function provideProcessData(): iterable
    {
        yield 'Cannot rerun exception' => [
            Webhook::fromArray([
                WebhookOption::Status->value => WebhookStatus::Success->value,
            ]),
            null,
            null,
            CannotRerunWebhookException::class,
        ];

        yield 'Can rerun, reset status and current attempt' => [
            Webhook::fromArray([
                WebhookOption::Status->value => WebhookStatus::Success->value,
            ])->allowRerun(),
            WebhookStatus::Pending,
            WebhookInterface::DEFAULT_CURRENT_ATTEMPT,
        ];

        yield 'Not a rerun, send through stack' => [
            Webhook::fromArray([
                WebhookOption::CurrentAttempt->value => 4,
                WebhookOption::Status->value => WebhookStatus::FailedPendingRetry->value,
            ])->allowRerun(),
            WebhookStatus::FailedPendingRetry,
            4,
        ];
    }

    /**
     * @phpstan-param class-string<\Throwable>|null $exceptedException
     */
    #[DataProvider('provideProcessData')]
    public function testProcess(
        WebhookInterface $webhook,
        ?WebhookStatus $expectedStatus = null,
        ?int $expectedCurrentAttempt = null,
        ?string $exceptedException = null,
    ): void {
        if ($exceptedException !== null) {
            $this->expectException($exceptedException);
        }

        $middleware = new RerunMiddleware();
        $result = $this->process($middleware, $webhook);

        self::assertSame($expectedStatus, $result->getWebhook()->getStatus());
        self::assertSame($expectedCurrentAttempt, $result->getWebhook()->getCurrentAttempt());
    }
}
