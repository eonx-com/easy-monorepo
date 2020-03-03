<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Tests\Bridge\Symfony\DependencyInjection;

use EonX\EasyAsync\Bridge\Symfony\DependencyInjection\Configuration;
use EonX\EasyAsync\Tests\AbstractTestCase;

final class ConfigurationTest extends AbstractTestCase
{
    /**
     * DataProvider for testGetConfigTreeBuilder.
     *
     * @return iterable<mixed>
     */
    public function providerGetConfigTreeBuilder(): iterable
    {
        yield 'Default values' => [
            [],
            [
                'implementation' => 'doctrine',
                'jobs_table' => 'easy_async_jobs',
                'job_logs_table' => 'easy_async_job_logs'
            ]
        ];

        yield 'Custom implementation' => [
            ['implementation' => 'custom'],
            [
                'implementation' => 'custom',
                'jobs_table' => 'easy_async_jobs',
                'job_logs_table' => 'easy_async_job_logs'
            ]
        ];
    }

    /**
     * TreeBuilder should build expected configuration array.
     *
     * @param mixed[] $value
     * @param mixed[] $expected
     *
     * @return void
     *
     * @dataProvider providerGetConfigTreeBuilder
     */
    public function testGetConfigTreeBuilder(array $value, array $expected): void
    {
        $treeBuilder = (new Configuration())->getConfigTreeBuilder();

        self::assertEquals($expected, $treeBuilder->buildTree()->finalize($value));
    }
}
