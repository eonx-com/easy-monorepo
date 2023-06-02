<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Aws;

use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Aws\SsmClient;
use EonX\EasySsm\Services\Parameters\Data\Diff;
use EonX\EasySsm\Tests\AbstractTestCase;
use EonX\EasySsm\Tests\Stubs\BaseSsmClientStub;

final class SsmClientTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testApplyDiff
     */
    public function providerTestApplyDiff(): iterable
    {
        yield '1 new param' => [
            new Diff([new SsmParameter('param', 'String', 'value')], [], []),
            [
                'put' => [
                    [
                        'Name' => 'param',
                        'Type' => 'String',
                        'Value' => 'value',
                    ],
                ],
                'delete' => [],
            ],
        ];

        yield '1 new, 1 updated, 1 deleted' => [
            new Diff(
                [new SsmParameter('param', 'String', 'value')],
                [new SsmParameter('param1', 'SecureString', 'value')],
                [new SsmParameter('param2', 'String', 'value')],
            ),
            [
                'put' => [
                    [
                        'Name' => 'param',
                        'Type' => 'String',
                        'Value' => 'value',
                    ],
                    [
                        'Name' => 'param1',
                        'Type' => 'SecureString',
                        'Value' => 'value',
                        'Overwrite' => true,
                    ],
                ],
                'delete' => [
                    [
                        'Name' => 'param2',
                    ],
                ],
            ],
        ];

        yield 'No diff' => [
            new Diff([], [], []),
            [
                'put' => [],
                'delete' => [],
            ],
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testGetAllParameters
     */
    public function providerTestGetAllParameters(): iterable
    {
        yield '1 param no path' => [
            [[
                'Name' => 'param',
                'Type' => 'String',
                'Value' => 'value',
            ]],
            [new SsmParameter('param', 'String', 'value')],
        ];

        yield '1 param trim value' => [
            [[
                'Name' => 'param',
                'Type' => 'String',
                'Value' => 'value        ',
            ]],
            [new SsmParameter('param', 'String', 'value')],
        ];

        yield '1 param path' => [
            [[
                'Name' => '/test/param',
                'Type' => 'String',
                'Value' => 'value',
            ]],
            [new SsmParameter('/test/param', 'String', 'value')],
            '/test',
        ];
    }

    /**
     * @param mixed[] $expected
     *
     * @dataProvider providerTestApplyDiff
     */
    public function testApplyDiff(Diff $diff, array $expected): void
    {
        $stub = new BaseSsmClientStub();
        $ssmClient = new SsmClient($stub);

        $ssmClient->applyDiff($diff);

        self::assertEquals($expected, $stub->getActions());
    }

    /**
     * @param mixed[] $parameters
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $expected
     *
     * @dataProvider providerTestGetAllParameters
     */
    public function testGetAllParameters(array $parameters, array $expected, ?string $path = null): void
    {
        $stub = new BaseSsmClientStub($parameters);
        $ssmClient = new SsmClient($stub);

        $expectedPaginatorCall = [
            'GetParametersByPath' => [
                'Path' => $path ?? '/',
                'Recursive' => true,
                'WithDecryption' => true,
            ],
        ];

        self::assertEquals($expected, $ssmClient->getAllParameters($path));
        self::assertEquals($expectedPaginatorCall, $stub->getPaginatorCalls());
    }
}
