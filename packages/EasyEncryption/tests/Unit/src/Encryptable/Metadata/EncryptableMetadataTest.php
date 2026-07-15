<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Metadata;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalisation;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;

final class EncryptableMetadataTest extends AbstractUnitTestCase
{
    public function testGetHashNormalisationsForFieldSucceedsForComposedOverride(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalisationsForField(EncryptableEntityStub::class, 'username');

        self::assertSame([HashNormalisation::Lowercase, HashNormalisation::Trim], $result);
    }

    public function testGetHashNormalisationsForFieldSucceedsForExplicitEmptyOverride(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalisationsForField(EncryptableEntityStub::class, 'caseSensitiveCode');

        self::assertSame([], $result);
    }

    public function testGetHashNormalisationsForFieldSucceedsForUnknownProperty(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalisationsForField(EncryptableEntityStub::class, 'doesNotExist');

        self::assertNull($result);
    }

    public function testGetHashNormalisationsForFieldSucceedsIfFieldHasNoOverride(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalisationsForField(EncryptableEntityStub::class, 'email');

        self::assertNull($result);
    }

    public function testGetHashNormalisationsForFieldSucceedsWithEntityInstance(): void
    {
        $metadata = new EncryptableMetadata();
        $entity = new EncryptableEntityStub();

        $result = $metadata->getHashNormalisationsForField($entity, 'username');

        self::assertSame([HashNormalisation::Lowercase, HashNormalisation::Trim], $result);
    }
}
