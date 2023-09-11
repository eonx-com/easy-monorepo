<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Events;

use EonX\EasyWebhook\Events\SuccessWebhookEvent;
use EonX\EasyWebhook\Tests\AbstractTestCase;
use EonX\EasyWebhook\Webhook;
use EonX\EasyWebhook\WebhookResult;

final class SuccessWebhookEventTest extends AbstractTestCase
{
    public function testGetResult(): void
    {
        $result = new WebhookResult(new Webhook());
        $event = new SuccessWebhookEvent($result);

        self::assertEquals(\spl_object_hash($result), \spl_object_hash($event->getResult()));
    }
}
