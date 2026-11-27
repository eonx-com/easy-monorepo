<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Configurator;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use EonX\EasyBugsnag\Common\Enum\ServerParam;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class SensitiveDataSanitizerClientConfiguratorTest extends AbstractUnitTestCase
{
    public function testItSucceedsAndDoNotSanitizeData(): void
    {
        self::bootKernel(['environment' => 'sensitive_data_sanitizer_off']);
        $client = self::getService(Client::class);
        $client->leaveBreadcrumb('test', Breadcrumb::MANUAL_TYPE, [
            'card_number' => '4111111111111111',
        ]);

        $client->notifyError('test', 'test');

        /** @var \Bugsnag\HttpClient $httpClient */
        $httpClient = self::getPrivatePropertyValue($client, 'http');
        /** @var \Bugsnag\Report[] $reports */
        $reports = self::getPrivatePropertyValue($httpClient, 'queue');
        $report = $reports[0];
        /** @var array $breadcrumbs */
        $breadcrumbs = self::getPrivatePropertyValue($report, 'breadcrumbs');
        self::assertSame('4111111111111111', $breadcrumbs[0]['metaData']['card_number']);
    }

    public function testItSucceedsAndSanitizeData(): void
    {
        $client = self::getService(Client::class);
        $client->leaveBreadcrumb('test', Breadcrumb::MANUAL_TYPE, [
            'card_number' => '4111111111111111',
        ]);

        $client->notifyError('test', 'test');

        /** @var \Bugsnag\HttpClient $httpClient */
        $httpClient = self::getPrivatePropertyValue($client, 'http');
        /** @var \Bugsnag\Report[] $reports */
        $reports = self::getPrivatePropertyValue($httpClient, 'queue');
        $report = $reports[0];
        /** @var array $breadcrumbs */
        $breadcrumbs = self::getPrivatePropertyValue($report, 'breadcrumbs');
        self::assertSame('*REDACTED*', $breadcrumbs[0]['metaData']['card_number']);
    }

    public function testItSucceedsAndSanitizesContextAndExceptionMessages(): void
    {
        // In CLI the client builds the context from argv unless it is told to resolve the HTTP request
        $_SERVER[ServerParam::ResolveRequestInCli->value] = true;
        self::getService(RequestStack::class)->push(Request::create('/reset-password/token/context-secret'));
        $client = self::getService(Client::class);
        $throwable = new RuntimeException(
            'HTTP 401 returned for "https://example.com/v1?token=top-secret&a=1".',
            0,
            new RuntimeException('Card 4111111111111111 declined, password=previous-secret')
        );

        try {
            $client->notifyException($throwable);
        } finally {
            unset($_SERVER[ServerParam::ResolveRequestInCli->value]);
        }

        /** @var \Bugsnag\HttpClient $httpClient */
        $httpClient = self::getPrivatePropertyValue($client, 'http');
        /** @var \Bugsnag\Report[] $reports */
        $reports = self::getPrivatePropertyValue($httpClient, 'queue');
        $report = $reports[0]->toArray();
        self::assertSame('GET /reset-password/token/*REDACTED*', $report['context']);
        self::assertSame(
            'HTTP 401 returned for "https://example.com/v1?token=*REDACTED*&a=1".',
            $report['exceptions'][0]['message']
        );
        self::assertSame(
            'Card 411111*REDACTED*1111 declined, password=*REDACTED*',
            $report['exceptions'][1]['message']
        );
    }
}
