<?php

declare(strict_types=1);

namespace EonX\EasySsm\Tests\Services\Hash;

use EonX\EasySsm\Helpers\Parameters;
use EonX\EasySsm\Services\Aws\Data\SsmParameter;
use EonX\EasySsm\Services\Hash\HashCalculator;
use EonX\EasySsm\Services\Hash\HashChecker;
use EonX\EasySsm\Tests\AbstractTestCase;
use EonX\EasySsm\Tests\Stubs\HashRepositoryStub;

final class HashCheckerTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     *
     * @see testCheckHash
     */
    public static function providerTestCheckHash(): iterable
    {
        yield 'No local hash' => ['no-local', [], false];

        yield 'Local hash different' => [
            'different',
            [new SsmParameter('param', 'string', 'value')],
            false,
            'different-hash',
        ];

        yield 'Same hashes' => [
            'same',
            [new SsmParameter('param', 'string', 'value')],
            true,
            '9dbcb3c2b77532e137e92da3b284328e',
        ];
    }

    /**
     * @return iterable<mixed>
     *
     * @see testCheckHashesForParams
     */
    public static function providerTestCheckHashesForParams(): iterable
    {
        yield 'Different because empty array' => [[new SsmParameter('param', 'string', 'value')], [], false];

        yield 'Different because name' => [
            [new SsmParameter('param', 'string', 'value')],
            [new SsmParameter('param1', 'string', 'value')],
            false,
        ];

        yield 'Different because type' => [
            [new SsmParameter('param', 'string', 'value')],
            [new SsmParameter('param', 'string1', 'value')],
            false,
        ];

        yield 'Different because value' => [
            [new SsmParameter('param', 'string', 'value')],
            [new SsmParameter('param', 'string', 'value1')],
            false,
        ];

        yield 'Different because everything' => [
            [new SsmParameter('param', 'string', 'value')],
            [new SsmParameter('param1', 'string1', 'value1')],
            false,
        ];

        yield 'Identical with empty arrays' => [[], [], true];

        yield 'Identical' => [
            [new SsmParameter('param', 'string', 'value')],
            [new SsmParameter('param', 'string', 'value')],
            true,
        ];
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $parameters
     *
     * @dataProvider providerTestCheckHash
     */
    public function testCheckHash(string $name, array $parameters, bool $expected, ?string $localHash = null): void
    {
        $hashRepository = new HashRepositoryStub([
            $name => $localHash,
        ]);
        $hashChecker = new HashChecker(new HashCalculator(new Parameters()), $hashRepository);

        self::assertEquals($expected, $hashChecker->checkHash($name, $parameters));
    }

    /**
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params1
     * @param \EonX\EasySsm\Services\Aws\Data\SsmParameter[] $params2
     *
     * @dataProvider providerTestCheckHashesForParams
     */
    public function testCheckHashesForParams(array $params1, array $params2, bool $expected): void
    {
        $hashRepository = new HashRepositoryStub();
        $hashChecker = new HashChecker(new HashCalculator(new Parameters()), $hashRepository);

        self::assertEquals($expected, $hashChecker->checkHashesForParams($params1, $params2));
    }
}
