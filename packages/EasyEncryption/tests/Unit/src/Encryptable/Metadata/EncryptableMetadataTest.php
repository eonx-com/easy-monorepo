<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Metadata;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;

final class EncryptableMetadataTest extends AbstractUnitTestCase
{
    public function testGetHashNormalizationsForFieldSucceedsForComposedOverride(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalizationsForField(EncryptableEntityStub::class, 'username');

        self::assertSame([HashNormalization::Lowercase, HashNormalization::Trim], $result);
    }

    public function testGetHashNormalizationsForFieldSucceedsForExplicitEmptyOverride(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalizationsForField(EncryptableEntityStub::class, 'caseSensitiveCode');

        self::assertSame([], $result);
    }

    public function testGetHashNormalizationsForFieldSucceedsForUnknownProperty(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalizationsForField(EncryptableEntityStub::class, 'doesNotExist');

        self::assertNull($result);
    }

    public function testGetHashNormalizationsForFieldSucceedsIfFieldHasNoOverride(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalizationsForField(EncryptableEntityStub::class, 'email');

        self::assertNull($result);
    }

    public function testGetHashNormalizationsForFieldSucceedsWithEntityInstance(): void
    {
        $metadata = new EncryptableMetadata();
        $entity = new EncryptableEntityStub();

        $result = $metadata->getHashNormalizationsForField($entity, 'username');

        self::assertSame([HashNormalization::Lowercase, HashNormalization::Trim], $result);
    }
}
