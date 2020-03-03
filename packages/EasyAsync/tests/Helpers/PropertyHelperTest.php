<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Helpers;

use EonX\EasyAsync\Data\Job;
use EonX\EasyAsync\Data\JobLog;
use EonX\EasyAsync\Data\Target;
use EonX\EasyAsync\Generators\DateTimeGenerator;
use EonX\EasyAsync\Helpers\PropertyHelper;
use EonX\EasyAsync\Interfaces\JobInterface;
use EonX\EasyAsync\Interfaces\JobLogInterface;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class PropertyHelperTest extends AbstractTestCase
{
    /**
     * DataProvider for testGetSetterName.
     *
     * @return iterable<mixed>
     */
    public function providerGetSetterName(): iterable
    {
        yield 'simple' => ['status', 'setStatus'];
        yield 'underscore' => ['debug_info', 'setDebugInfo'];
    }

    /**
     * DataProvider for testSetDatetimeProperties.
     *
     * @return iterable<mixed>
     */
    public function providerSetDatetimeProperties(): iterable
    {
        yield 'set startedAt only' => [
            ['started_at' => '2019-08-01 00:00:00', 'finished_at' => ''],
            ['started_at', 'finished_at'],
            static function (JobInterface $job): void {
                self::assertNull($job->getFinishedAt());
                self::assertInstanceOf(\DateTime::class, $job->getStartedAt());
            }
        ];
    }

    /**
     * DataProvider for testSetIntProperties.
     *
     * @return iterable<mixed>
     */
    public function providerSetIntProperties(): iterable
    {
        yield 'set total' => [
            ['failed' => '10'],
            ['failed'],
            static function (JobInterface $job): void {
                self::assertEquals(10, $job->getFailed());
            }
        ];
    }

    /**
     * DataProvider for testSetJsonProperties.
     *
     * @return iterable<mixed>
     */
    public function providerSetJsonProperties(): iterable
    {
        yield 'set debug_info' => [
            ['debug_info' => '{"key":"value"}'],
            ['debug_info'],
            static function (JobLogInterface $jobLog): void {
                self::assertEquals(['key' => 'value'], $jobLog->getDebugInfo());
            }
        ];
    }

    /**
     * DataProvider for testSetOptionalProperties.
     *
     * @return iterable<mixed>
     */
    public function providerSetOptionalProperties(): iterable
    {
        yield 'set failure_reason' => [
            ['failure_reason' => 'reason'],
            ['failure_reason'],
            static function (JobLogInterface $jobLog): void {
                self::assertEquals('reason', $jobLog->getFailureReason());
            }
        ];
    }

    /**
     * Helper should return expected setter name for given property.
     *
     * @param string $property
     * @param string $setterName
     *
     * @return void
     *
     * @dataProvider providerGetSetterName
     */
    public function testGetSetterName(string $property, string $setterName): void
    {
        self::assertEquals($setterName, PropertyHelper::getSetterName($property));
    }

    /**
     * Helper should set datetime properties on job.
     *
     * @param mixed[] $data
     * @param mixed[] $properties
     * @param callable $test
     *
     * @return void
     *
     * @throws \EonX\EasyAsync\Exceptions\UnableToGenerateDateTimeException
     *
     * @dataProvider providerSetDatetimeProperties
     */
    public function testSetDatetimeProperties(array $data, array $properties, callable $test): void
    {
        $job = new Job(new Target('id', 'target'), 'test');

        PropertyHelper::setDatetimeProperties($job, $data, $properties, new DateTimeGenerator());

        \call_user_func($test, $job);
    }

    /**
     * Helper should set integer properties on job.
     *
     * @param mixed[] $data
     * @param mixed[] $properties
     * @param callable $test
     *
     * @return void
     *
     * @dataProvider providerSetIntProperties
     */
    public function testSetIntProperties(array $data, array $properties, callable $test): void
    {
        $job = new Job(new Target('id', 'target'), 'test');

        PropertyHelper::setIntProperties($job, $data, $properties);

        \call_user_func($test, $job);
    }

    /**
     * Helper should set json properties on job log.
     *
     * @param mixed[] $data
     * @param mixed[] $properties
     * @param callable $test
     *
     * @return void
     *
     * @throws \Nette\Utils\JsonException
     *
     * @dataProvider providerSetJsonProperties
     */
    public function testSetJsonProperties(array $data, array $properties, callable $test): void
    {
        $jobLog = new JobLog(new Target('id', 'type'), 'test', 'jobId');

        PropertyHelper::setJsonProperties($jobLog, $data, $properties);

        \call_user_func($test, $jobLog);
    }

    /**
     * Helper should set optional properties on job log.
     *
     * @param mixed[] $data
     * @param mixed[] $properties
     * @param callable $test
     *
     * @return void
     *
     * @dataProvider providerSetOptionalProperties
     */
    public function testSetOptionalProperties(array $data, array $properties, callable $test): void
    {
        $jobLog = new JobLog(new Target('id', 'type'), 'test', 'jobId');

        PropertyHelper::setOptionalProperties($jobLog, $data, $properties);

        \call_user_func($test, $jobLog);
    }
}
