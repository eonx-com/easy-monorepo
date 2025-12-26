<?php
declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Unit\Common\Configurator;

use Bugsnag\Client;
use Bugsnag\Configuration;
use Bugsnag\Report;
use EonX\EasyBugsnag\Common\Configurator\AwsEcsFargateClientConfigurator;
use EonX\EasyBugsnag\Tests\Unit\AbstractUnitTestCase;
use RuntimeException;

final class AwsEcsFargateClientConfiguratorTest extends AbstractUnitTestCase
{
    public function testAppVersionDefaultsToNullWhenIssue(): void
    {
        $bugsnag = new Client(new Configuration('my-api-key'));
        $configurator = new AwsEcsFargateClientConfigurator('invalid', 'invalid');

        $configurator->configure($bugsnag);

        self::assertArrayNotHasKey('version', $bugsnag->getAppData());
    }

    public function testReportHasAwsErrorWhenIssue(): void
    {
        $bugsnag = new Client(new Configuration('my-api-key'));
        $report = Report::fromPHPThrowable($bugsnag->getConfig(), new RuntimeException('message'));

        new AwsEcsFargateClientConfigurator('invalid', 'invalid')
->configure($bugsnag);
        $bugsnag->getPipeline()
            ->execute($report, function (): void {
            });

        self::assertArrayHasKey('Error', $report->getMetaData()['aws']);
    }
}
