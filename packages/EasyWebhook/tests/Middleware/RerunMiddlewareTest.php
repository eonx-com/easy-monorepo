<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Middleware;

use EonX\EasyWebhook\Exceptions\CannotRerunWebhookException;
use EonX\EasyWebhook\Interfaces\WebhookInterface;
use EonX\EasyWebhook\Middleware\RerunMiddleware;
use EonX\EasyWebhook\Tests\AbstractMiddlewareTestCase;
use EonX\EasyWebhook\Webhook;

final class RerunMiddlewareTest extends AbstractMiddlewareTestCase
{
    /**
     * @see testProcess
     */
    public static function providerTestProcess(): iterable
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
     *
     * @dataProvider providerTestProcess
     */
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
