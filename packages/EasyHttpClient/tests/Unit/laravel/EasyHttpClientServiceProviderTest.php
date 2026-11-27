<?php
declare(strict_types=1);

namespace EonX\EasyHttpClient\Tests\Unit\Laravel;

use Carbon\CarbonImmutable;
use EonX\EasyEventDispatcher\Laravel\EasyEventDispatcherServiceProvider;
use EonX\EasyHttpClient\Common\Event\HttpRequestSentEvent;
use EonX\EasyHttpClient\Common\HttpClient\WithEventsHttpClient;
use EonX\EasyHttpClient\Common\ValueObject\RequestData;
use EonX\EasyHttpClient\EasyBugsnag\Listener\HttpRequestSentBreadcrumbListener;
use EonX\EasyHttpClient\Tests\Stub\Logger\LoggerStub;
use EonX\EasyWebhook\Bundle\Enum\ConfigServiceId as EasyWebhookConfigServiceId;
use EonX\EasyWebhook\Laravel\EasyWebhookServiceProvider;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;

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

    public function testAwsMessagingRequestSanitizerListenerRunsBeforeLogListener(): void
    {
        $app = $this->getApp([
            'easy-http-client' => [
                'easy_bugsnag' => [
                    'enabled' => false,
                ],
            ],
        ]);
        $logger = new LoggerStub();
        $app->instance(LoggerInterface::class, $logger);

        $app->make('events')
            ->dispatch(new HttpRequestSentEvent(new RequestData(
                method: 'POST',
                options: [
                    'body' => 'Action=Publish&Message=Your+code+is+123456',
                ],
                sentAt: CarbonImmutable::parse('2026-01-01 00:00:00'),
                url: 'https://sns.ap-southeast-2.amazonaws.com/'
            )));

        self::assertSame(
            'Action=Publish&Message=*REDACTED*',
            $logger->getRecords()[0]['context']['http_options']['body']
        );
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

        self::assertSame(
            $enabled,
            \in_array(
                HttpRequestSentBreadcrumbListener::class,
                $app->make('events')
                    ->getRawListeners()[HttpRequestSentEvent::class],
                true
            )
        );
    }
}
