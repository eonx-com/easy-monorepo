<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use EonX\EasyUtils\EnvVarSubstitutionHelper;

final class EnvVarSubstitutionHelperTest extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestResolveVariables(): iterable
    {
        yield 'With $ in value' => [
            ['password' => 'qLiByxNIXT5Gg11zt$2PjHb952nnVEZK'],
            ['password' => 'qLiByxNIXT5Gg11zt$2PjHb952nnVEZK'],
        ];
    }

    /**
     * @param mixed[] $input
     * @param mixed[] $expected
     *
     * @dataProvider providerTestResolveVariables
     */
    public function testResolveVariables(array $input, array $expected): void
    {
        \dump(EnvVarSubstitutionHelper::resolveVariables($input));

        self::assertEquals($expected, EnvVarSubstitutionHelper::resolveVariables($input));
    }
}
