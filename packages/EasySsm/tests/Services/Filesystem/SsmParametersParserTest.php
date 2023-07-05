<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Filesystem;

use EonX\EasySsm\Helpers\Arr;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Filesystem\SsmParametersParser;
use EonX\EasySsm\Tests\AbstractTestCase;

final class SsmParametersParserTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testParseParameters
     */
    public static function providerTestParseParameters(): iterable
    {
        yield '1 simple param' => [
            [new SsmParameter('/param', 'String', 'value')],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/1_simple_param.yaml',
        ];

        yield '1 namespaced param' => [
            [new SsmParameter('/test/test/param', 'String', 'value')],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/1_namespaced_param.yaml',
        ];

        yield '1 namespaced secure param' => [
            [new SsmParameter('/test/test/param', 'SecureString', 'value')],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/1_namespaced_secure_param.yaml',
        ];

        yield '2 namespaces' => [
            [
                new SsmParameter('/dev/dev/param', 'String', 'value'),
                new SsmParameter('/test/test/param', 'String', 'value'),
            ],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/2_namespaces.yaml',
        ];

        yield 'multilines param values' => [
            [
                new SsmParameter('/not_secure', 'String', "my\nmultiline\nvalue"),
                new SsmParameter('/secure', 'SecureString', "my\nmultiline\nvalue"),
            ],
            __DIR__ . '/../../Fixtures/SsmParametersDumper/multilines_param_values.yaml',
        ];
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @dataProvider providerTestParseParameters
     */
    public function testParseParameters(array $parameters, string $fixtureFile): void
    {
        $ssmParametersParser = new SsmParametersParser(new Arr());

        self::assertEquals($parameters, $ssmParametersParser->parseParameters($fixtureFile));
    }
}
