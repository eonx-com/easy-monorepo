<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Middleware;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Exception\CannotRerunWebhookException;
use EonX\EasyWebhook\Common\Middleware\HandleExceptionsMiddleware;
use Exception;

final class HandleExceptionsMiddlewareTest extends AbstractMiddlewareTestCase
{
    public function testAnyExceptionThrownReturnsFailedResult(): void
    {
        $middleware = new HandleExceptionsMiddleware();
        $webhook = Webhook::create('https://eonx.com');
        $throwable = new Exception('message');

        $result = $this->processWithThrowable($middleware, $webhook, $throwable);

        self::assertFalse($result->isSuccessful());
        self::assertSame($throwable, $result->getThrowable());
    }

    public function testDoNotHandleMeExceptionIsThrown(): void
    {
        $this->expectException(CannotRerunWebhookException::class);

        $middleware = new HandleExceptionsMiddleware();
        $webhook = Webhook::create('https://eonx.com');
        $throwable = new CannotRerunWebhookException('message');

        $this->processWithThrowable($middleware, $webhook, $throwable);
    }
}
