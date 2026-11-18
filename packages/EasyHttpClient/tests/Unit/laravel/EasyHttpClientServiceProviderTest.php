<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Unit\Laravel;

use EonX\EasyEventDispatcher\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\HttpClient\WithEventsHttpClient;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId as EasyWebhookConfigServiceId;
use EonX\EasyWebhook\Laravel\EasyWebhookServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyHttpClientServiceProviderTest extends AbstractLaravelTestCase
{
    /**
     * @see testEasyBugsnagListenerFollowsConfig
     */
    public static function provideEasyBugsnagEnabledValues(): iterable
    {
        yield 'enabled' => [true];

        yield 'disabled' => [false];
    }

    public function testDecorateEasyWebhookClient(): void
    {
        $app = $this->getApp([
            'easy-http-client' => [
                'decorate_easy_webhook_client' => true,
            ],
        ], [
            EasyEventDispatcherServiceProvider::class,
            EasyWebhookServiceProvider::class,
        ]);

        self::assertInstanceOf(
            WithEventsHttpClient::class,
            $app->get(EasyWebhookConfigServiceId::HttpClient->value)
        );
    }

    #[DataProvider('provideEasyBugsnagEnabledValues')]
    public function testEasyBugsnagListenerFollowsConfig(bool $enabled): void
    {
        $app = $this->getApp([
            'easy-http-client' => [
                'easy_bugsnag' => [
                    'enabled' => $enabled,
                ],
                'psr_logger' => [
                    'enabled' => false,
                ],
            ],
        ]);

        self::assertSame($enabled, $app->make('events')->hasListeners(HttpRequestSentEvent::class));
    }
}
