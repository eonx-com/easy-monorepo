<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Normaliser;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalisation;
use EonX\EasyEncryption\Encryptable\Normaliser\HashNormaliser;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HashNormaliserTest extends AbstractUnitTestCase
{
    /**
     * @see testNormaliseSucceeds
     */
    public static function provideNormaliseData(): iterable
    {
        yield 'lowercase' => [HashNormalisation::Lowercase, 'John.Doe@Example.com', 'john.doe@example.com'];

        yield 'trim' => [HashNormalisation::Trim, "  padded value \t", 'padded value'];
    }

    #[DataProvider('provideNormaliseData')]
    public function testNormaliseSucceeds(HashNormalisation $normalisation, string $value, string $expected): void
    {
        $normaliser = new HashNormaliser();

        $result = $normaliser->normalise($value, $normalisation);

        self::assertSame($expected, $result);
    }

    public function testNormaliseSucceedsForComposedNormalisations(): void
    {
        $normaliser = new HashNormaliser();
        $value = '  John.Doe@Example.com  ';

        foreach ([HashNormalisation::Trim, HashNormalisation::Lowercase] as $normalisation) {
            $value = $normaliser->normalise($value, $normalisation);
        }

        self::assertSame('john.doe@example.com', $value);
    }
}
