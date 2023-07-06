<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Hash;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Hash\HashCalculator;
use EonX\EasySsm\Tests\AbstractTestCase;

final class HashCalculatorTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testCalculateHash
     */
    public static function providerTestCalculateHash(): iterable
    {
        yield '1 string parameter' => [
            [new SsmParameter('param', 'string', 'value')],
            '9dbcb3c2b77532e137e92da3b284328e',
        ];

        yield '2 string parameters' => [
            [new SsmParameter('param', 'string', 'value'), new SsmParameter('param1', 'string', 'value1')],
            '468426c6f1f39a846e871a1a58cab1db',
        ];

        yield '2 string parameters different order' => [
            [new SsmParameter('param1', 'string', 'value1'), new SsmParameter('param', 'string', 'value')],
            '468426c6f1f39a846e871a1a58cab1db',
        ];
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @throws \Nette\Utils\JsonException
     *
     * @dataProvider providerTestCalculateHash
     */
    public function testCalculateHash(array $parameters, string $expected): void
    {
        self::assertEquals($expected, (new HashCalculator(new Parameters()))->calculate($parameters));
    }
}
