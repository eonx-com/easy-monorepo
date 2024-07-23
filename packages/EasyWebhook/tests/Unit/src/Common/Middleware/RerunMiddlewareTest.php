<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookInterface;
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
                WebhookInterface::OPTION_STATUS => WebhookInterface::STATUS_SUCCESS,
            ]),
            null,
            null,
            CannotRerunWebhookException::class,
        ];

        yield 'Can rerun, reset status and current attempt' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_STATUS => WebhookInterface::STATUS_SUCCESS,
            ])->allowRerun(),
            WebhookInterface::STATUS_PENDING,
            WebhookInterface::DEFAULT_CURRENT_ATTEMPT,
        ];

        yield 'Not a rerun, send through stack' => [
            Webhook::fromArray([
                WebhookInterface::OPTION_CURRENT_ATTEMPT => 4,
                WebhookInterface::OPTION_STATUS => WebhookInterface::STATUS_FAILED_PENDING_RETRY,
            ])->allowRerun(),
            WebhookInterface::STATUS_FAILED_PENDING_RETRY,
            4,
        ];
    }

    /**
     * @phpstan-param class-string<\Throwable>|null $exceptedException
     */
    #[DataProvider('provideProcessData')]
    public function testProcess(
        WebhookInterface $webhook,
        ?string $expectedStatus = null,
        ?int $expectedCurrentAttempt = null,
        ?string $exceptedException = null,
    ): void {
        if ($exceptedException !== null) {
            $this->expectException($exceptedException);
        }

        $middleware = new RerunMiddleware();
        $result = $this->process($middleware, $webhook);

        self::assertEquals($expectedStatus, $result->getWebhook()->getStatus());
        self::assertEquals($expectedCurrentAttempt, $result->getWebhook()->getCurrentAttempt());
    }
}
