<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Application\Bundle;

use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\HttpClient\WithEventsHttpClient;
use EonX\EasyHttpClient\Common\Listener\AwsMessagingRequestSanitizerListener;
use EonX\EasyHttpClient\Tests\Application\AbstractApplicationTestCase;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId as EasyWebhookConfigServiceId;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class EasyHttpClientBundleTest extends AbstractApplicationTestCase
{
    public function testAwsMessagingRequestSanitizerListenerRunsBeforeListenersOfPriorityZero(): void
    {
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);

        /** @var callable $listener */
        $listener = $eventDispatcher->getListeners(HttpRequestSentEvent::class)[0];

        self::assertInstanceOf(
            AwsMessagingRequestSanitizerListener::class,
            \is_array($listener) ? $listener[0] : $listener
        );
        self::assertGreaterThan(0, $eventDispatcher->getListenerPriority(HttpRequestSentEvent::class, $listener));
    }

    public function testDecorateEasyWebhookClient(): void
    {
        self::assertInstanceOf(
            WithEventsHttpClient::class,
            self::getContainer()->get(EasyWebhookConfigServiceId::HttpClient->value)
        );
    }
}
