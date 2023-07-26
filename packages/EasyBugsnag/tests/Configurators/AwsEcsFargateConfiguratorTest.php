<?php

declare(strict_types=1);

namespace EonX\EasyBugsnag\Tests\Configurators;

use Bugsnag\Client;
use Bugsnag\Configuration;
use Bugsnag\Report;
use EonX\EasyBugsnag\Configurators\AwsEcsFargateConfigurator;
use EonX\EasyBugsnag\Tests\AbstractTestCase;
use RuntimeException;

final class AwsEcsFargateConfiguratorTest extends AbstractTestCase
{
    public function testAppVersionDefaultsToNullWhenIssue(): void
    {
        $bugsnag = new Client(new Configuration('my-api-key'));
        $configurator = new AwsEcsFargateConfigurator('invalid', 'invalid');

        $configurator->configure($bugsnag);

        self::assertArrayNotHasKey('version', $bugsnag->getAppData());
    }

    public function testReportHasAwsErrorWhenIssue(): void
    {
        $bugsnag = new Client(new Configuration('my-api-key'));
        $report = Report::fromPHPThrowable($bugsnag->getConfig(), new RuntimeException('message'));

        (new AwsEcsFargateConfigurator('invalid', 'invalid'))->configure($bugsnag);
        $bugsnag->getPipeline()
            ->execute($report, function (): void {
            });

        self::assertArrayHasKey('Error', $report->getMetaData()['aws']);
    }
}
