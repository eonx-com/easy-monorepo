<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests;

use EonX\EasyUtils\Helpers\EnvVarSubstitutionHelper;
use PHPUnit\Framework\Attributes\DataProvider;

final class EnvVarSubstitutionHelperTest extends AbstractTestCase
{
    /**
     * @see testResolveVariables
     */
    public static function providerTestResolveVariables(): iterable
    {
        yield 'With $ in value' => [
            ['password' => 'qLiByxT5Gg11zt$2PjHb952nnVEZK'],
            ['password' => 'qLiByxT5Gg11zt$2PjHb952nnVEZK'],
        ];
    }

    #[DataProvider('providerTestResolveVariables')]
    public function testResolveVariables(array $input, array $expected): void
    {
        self::assertEquals($expected, EnvVarSubstitutionHelper::resolveVariables($input));
    }
}
