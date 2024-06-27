<?php
declare(strict_types=1);

namespace EonX\EasyWebhook\Tests\Unit\Common\Event;

use EonX\EasyWebhook\Common\Entity\Webhook;
use EonX\EasyWebhook\Common\Entity\WebhookResult;
use EonX\EasyWebhook\Common\Event\SuccessWebhookEvent;
use EonX\EasyWebhook\Tests\Unit\AbstractUnitTestCase;

final class SuccessWebhookEventTest extends AbstractUnitTestCase
{
    public function testGetResult(): void
    {
        $result = new WebhookResult(new Webhook());
        $event = new SuccessWebhookEvent($result);

        self::assertEquals(\spl_object_hash($result), \spl_object_hash($event->getResult()));
    }
}
