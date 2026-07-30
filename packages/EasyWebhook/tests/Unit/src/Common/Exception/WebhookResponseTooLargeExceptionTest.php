<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Exception;

use EonX\EasyWebhook\Common\Exception\WebhookResponseTooLargeException;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;
use RuntimeException;

final class WebhookResponseTooLargeExceptionTest extends AbstractUnitTestCase
{
    public function testIsInChainDetectsDirectAndWrapped(): void
    {
        $direct = new WebhookResponseTooLargeException('too large');
        // Mirrors how SendWebhookMiddleware re-wraps it as the previous of WebhookRequestFailedException
        $wrapped = new RuntimeException('request failed', 0, $direct);

        self::assertTrue(WebhookResponseTooLargeException::isInChain($direct));
        self::assertTrue(WebhookResponseTooLargeException::isInChain($wrapped));
    }

    public function testIsInChainFalseForUnrelatedOrNull(): void
    {
        self::assertFalse(WebhookResponseTooLargeException::isInChain(new RuntimeException('other')));
        self::assertFalse(WebhookResponseTooLargeException::isInChain(null));
    }
}
