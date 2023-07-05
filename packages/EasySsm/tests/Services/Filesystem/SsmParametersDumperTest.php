<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Filesystem;

use EonX\EasySsm\Helpers\Arr;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Filesystem\SsmParametersDumper;
use EonX\EasySsm\Tests\AbstractTestCase;
use Symfony\Component\Filesystem\Filesystem;

final class SsmParametersDumperTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDumpParameters
     */
    public static function providerTestDumpParameters(): iterable
    {
        yield '1 simple param' => [
            [new SsmParameter('param', 'string', 'value')],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/1_simple_param.yaml',
        ];

        yield '1 namespaced param' => [
            [new SsmParameter('/test/test/param', 'string', 'value')],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/1_namespaced_param.yaml',
        ];

        yield '1 namespaced secure param' => [
            [new SsmParameter('/test/test/param', 'SecureString', 'value')],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/1_namespaced_secure_param.yaml',
        ];

        yield '2 namespaces' => [
            [
                new SsmParameter('/test/test/param', 'string', 'value'),
                new SsmParameter('/dev/dev/param', 'string', 'value'),
            ],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/2_namespaces.yaml',
        ];

        // TODO: Until https://github.com/symfony/symfony/pull/40431 is fixed
        //yield 'multilines param values' => [
        //    [
        //        new SsmParameter('secure', 'SecureString', "my\nmultiline\nvalue"),
        //        new SsmParameter('not_secure', 'String', "my\nmultiline\nvalue"),
        //    ],
        //    __DIR__ . '/../../Fixtures/SsmParametersDumper/multilines_param_values.yaml',
        //];
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @dataProvider providerTestDumpParameters
     */
    public function testDumpParameters(array $parameters, string $fixtureFile): void
    {
        $filename = __DIR__ . '/../../../var/test.yaml';
        $ssmParametersDumper = new SsmParametersDumper(new Arr(), new Filesystem());

        $ssmParametersDumper->dumpParameters($filename, $parameters);

        self::assertFileEquals($fixtureFile, $filename);
    }
}
