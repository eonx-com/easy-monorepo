<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Configurator;

use Bugsnag\Breadcrumbs\Breadcrumb;
use Bugsnag\Client;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;

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
        $breadcrumbs = self::getPrivatePropertyValue($report, 'breadcrumbs');
        self::assertSame('*REDACTED*', $breadcrumbs[0]['metaData']['card_number']);
    }
}
