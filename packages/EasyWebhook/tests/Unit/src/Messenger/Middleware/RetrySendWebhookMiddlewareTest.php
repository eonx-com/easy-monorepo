<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Messenger\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Exception\WebhookHttpClientOptionNotAllowedException;
use EonX\EasyWebhook\Common\Exception\WebhookRequestFailedException;
use EonX\EasyWebhook\Common\Exception\WebhookResponseTooLargeException;
use EonX\EasyWebhook\Common\Strategy\MultiplierWebhookRetryStrategy;
use EonX\EasyWebhook\Messenger\Message\SendWebhookMessage;
use EonX\EasyWebhook\Messenger\Middleware\RetrySendWebhookMiddleware;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\StackMiddleware;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;
use Throwable;

final class RetrySendWebhookMiddlewareTest extends AbstractUnitTestCase
{
    /**
     * @see testHandleSendsTheMessageBackOnlyForRetryableFailure
     */
    public static function provideFailedResultData(): iterable
    {
        yield 'request failed' => [new WebhookRequestFailedException('failed'), 1];

        yield 'HTTP client option not allowed' => [new WebhookHttpClientOptionNotAllowedException('not allowed'), 0];

        yield 'response too large' => [
            new WebhookRequestFailedException(
                message: 'failed',
                previous: new WebhookResponseTooLargeException('too large')
            ),
            0,
        ];
    }

    #[DataProvider('provideFailedResultData')]
    public function testHandleSendsTheMessageBackOnlyForRetryableFailure(
        Throwable $throwable,
        int $expectedSentMessages,
    ): void {
        $webhook = Webhook::create('https://eonx.com')->maxAttempt(3);
        $message = new SendWebhookMessage('webhook-id');
        $message->setResult(new WebhookResult($webhook, null, $throwable));
        $transport = new InMemoryTransport();
        $container = new Container();
        $container->set('async', $transport);
        $middleware = new RetrySendWebhookMiddleware($container, new MultiplierWebhookRetryStrategy());

        $middleware->handle(new Envelope($message, [new ReceivedStamp('async')]), new StackMiddleware());

        self::assertCount($expectedSentMessages, $transport->getSent());
    }
}
