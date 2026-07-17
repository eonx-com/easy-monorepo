<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Normalizer;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;
use EonX\EasyEncryption\Encryptable\Normalizer\HashNormalizer;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class HashNormalizerTest extends AbstractUnitTestCase
{
    /**
     * @see testNormalizeSucceeds
     */
    public static function provideNormalizeData(): iterable
    {
        yield 'lowercase' => [HashNormalization::Lowercase, 'John.Doe@Example.com', 'john.doe@example.com'];

        yield 'trim' => [HashNormalization::Trim, "  padded value \t", 'padded value'];
    }

    #[DataProvider('provideNormalizeData')]
    public function testNormalizeSucceeds(HashNormalization $normalization, string $value, string $expected): void
    {
        $normalizer = new HashNormalizer();

        $result = $normalizer->normalize($value, $normalization);

        self::assertSame($expected, $result);
    }

    public function testNormalizeSucceedsForComposedNormalizations(): void
    {
        $normalizer = new HashNormalizer();
        $value = '  John.Doe@Example.com  ';

        $value = $normalizer->normalize($value, HashNormalization::Trim);
        $value = $normalizer->normalize($value, HashNormalization::Lowercase);

        self::assertSame('john.doe@example.com', $value);
    }
}
