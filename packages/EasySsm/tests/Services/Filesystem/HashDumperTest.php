<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Filesystem;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Filesystem\HashDumper;
use EonX\EasySsm\Services\Hash\HashCalculator;
use EonX\EasySsm\Tests\AbstractTestCase;
use EonX\EasySsm\Tests\Stubs\HashRepositoryStub;

final class HashDumperTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testDumpHash
     */
    public static function providerTestDumpHash(): iterable
    {
        yield '1 param' => [
            'test',
            [new SsmParameter('param', 'string', 'value')],
            '9dbcb3c2b77532e137e92da3b284328e',
        ];

        yield '2 params' => [
            'test',
            [new SsmParameter('param', 'string', 'value'), new SsmParameter('param1', 'string', 'value')],
            '8ee15046ee89706b7f9936da70710215',
        ];

        yield '2 params different order' => [
            'test',
            [new SsmParameter('param1', 'string', 'value'), new SsmParameter('param', 'string', 'value')],
            '8ee15046ee89706b7f9936da70710215',
        ];
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @dataProvider providerTestDumpHash
     */
    public function testDumpHash(string $name, array $parameters, string $expected): void
    {
        $hashRepository = new HashRepositoryStub();
        $hashCalculator = new HashCalculator(new Parameters());

        (new HashDumper($hashCalculator, $hashRepository))->dumpHash($name, $parameters);

        self::assertEquals($expected, $hashRepository->get($name));
    }
}
