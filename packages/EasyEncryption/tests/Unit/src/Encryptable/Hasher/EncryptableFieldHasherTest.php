<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Hasher;

use EonX\EasyEncryption\Encryptable\HashCalculator\HmacSha512HashCalculator;
use EonX\EasyEncryption\Encryptable\Hasher\EncryptableFieldHasher;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Encryptable\Normalizer\HashNormalizer;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;

final class EncryptableFieldHasherTest extends AbstractUnitTestCase
{
    private const SECRET = 'test-secret';

    public function testHashForFieldSucceedsWhenFieldOverrideOptsOutOfLowercaseDefault(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        // Global default lowercases, but "caseSensitiveCode" explicitly requests no normalization
        $hasher = new EncryptableFieldHasher(
            $calculator,
            new EncryptableMetadata(),
            new HashNormalizer(),
            ['lowercase']
        );

        $hash = $hasher->hashForField(EncryptableEntityStub::class, 'caseSensitiveCode', 'AbC123');

        self::assertSame($calculator->calculate('AbC123'), $hash);
    }

    public function testHashForFieldSucceedsWithComposedNormalization(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        $hasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);

        $hash = $hasher->hashForField(EncryptableEntityStub::class, 'username', "  Jane.Doe  \t");

        self::assertSame($calculator->calculate('jane.doe'), $hash);
    }

    public function testHashForFieldSucceedsWithDifferentHashForDifferentCaseWithoutLowercasing(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        $hasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);

        $upperCaseHash = $hasher->hashForField(EncryptableEntityStub::class, 'email', 'John@Example.com');
        $lowerCaseHash = $hasher->hashForField(EncryptableEntityStub::class, 'email', 'john@example.com');

        self::assertNotSame($upperCaseHash, $lowerCaseHash);
    }

    public function testHashForFieldSucceedsWithFieldOverrideTakingPrecedence(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        // Global default is "no normalization", but the "username" field overrides with lowercase + trim
        $hasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);

        $hash = $hasher->hashForField(EncryptableEntityStub::class, 'username', '  John.Doe  ');

        self::assertSame($calculator->calculate('john.doe'), $hash);
    }

    public function testHashForFieldSucceedsWithLowercaseDefault(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        $hasher = new EncryptableFieldHasher(
            $calculator,
            new EncryptableMetadata(),
            new HashNormalizer(),
            ['lowercase']
        );

        $hash = $hasher->hashForField(EncryptableEntityStub::class, 'email', 'John.Doe@Example.com');

        self::assertSame($calculator->calculate('john.doe@example.com'), $hash);
    }

    public function testHashForFieldSucceedsWithSameHashForDifferentCaseWhenLowercasing(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        $hasher = new EncryptableFieldHasher(
            $calculator,
            new EncryptableMetadata(),
            new HashNormalizer(),
            ['lowercase']
        );

        $upperCaseHash = $hasher->hashForField(EncryptableEntityStub::class, 'email', 'John@Example.com');
        $lowerCaseHash = $hasher->hashForField(EncryptableEntityStub::class, 'email', 'john@example.com');

        self::assertSame($upperCaseHash, $lowerCaseHash);
    }

    public function testHashForFieldSucceedsWithoutCallerReproducingNormalization(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        $hasher = new EncryptableFieldHasher(
            $calculator,
            new EncryptableMetadata(),
            new HashNormalizer(),
            ['lowercase']
        );

        // A caller (e.g. a repository) only supplies entity class + field + raw value: it does not need to
        // know or reproduce which normalization applies to that field
        $writeHash = $hasher->hashForField(EncryptableEntityStub::class, 'username', 'Jane.Doe');
        $lookupHash = $hasher->hashForField(EncryptableEntityStub::class, 'username', 'jane.doe');

        self::assertSame($writeHash, $lookupHash);
    }

    public function testHashForFieldSucceedsWithoutNormalizationDefault(): void
    {
        $calculator = new HmacSha512HashCalculator(self::SECRET);
        $hasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);

        $hash = $hasher->hashForField(EncryptableEntityStub::class, 'email', 'John.Doe@Example.com');

        self::assertSame($calculator->calculate('John.Doe@Example.com'), $hash);
    }
}
