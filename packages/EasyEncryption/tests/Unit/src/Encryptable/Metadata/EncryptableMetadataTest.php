<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Metadata;

use EonX\EasyEncryption\Encryptable\Enum\HashNormalization;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableChildEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableDuplicateFieldEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableProxyEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableRedeclaredFieldEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableSameFieldNameEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableSameFieldNameInClassEntityStub;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;
use LogicException;

final class EncryptableMetadataTest extends AbstractUnitTestCase
{
    public function testGetEncryptableFieldNamesSucceedsWithPrivateFieldsOfParentClass(): void
    {
        $metadata = new EncryptableMetadata();

        self::assertSame(['email' => 'email'], $metadata->getEncryptableFieldNames(EncryptableProxyEntityStub::class));
        self::assertSame(
            [
                'email' => 'email',
                'secretCode' => 'secret_code',
            ],
            $metadata->getEncryptableFieldNames(EncryptableChildEntityStub::class)
        );
    }

    public function testGetEncryptableFieldNamesSucceedsWithProtectedFieldRedeclaredInChildClass(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getEncryptableFieldNames(EncryptableRedeclaredFieldEntityStub::class);

        self::assertSame(['code' => 'child_code'], $result);
    }

    public function testGetEncryptableFieldNamesThrowsOnSameEncryptedDataFieldInOneClass(): void
    {
        $metadata = new EncryptableMetadata();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The encrypted data field "email" is used by more than one property');

        $metadata->getEncryptableFieldNames(EncryptableSameFieldNameInClassEntityStub::class);
    }

    public function testGetEncryptableFieldNamesThrowsOnSameEncryptedDataFieldInParentAndChild(): void
    {
        $metadata = new EncryptableMetadata();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('secret_code');

        $metadata->getEncryptableFieldNames(EncryptableSameFieldNameEntityStub::class);
    }

    public function testGetEncryptableFieldNamesThrowsOnSamePrivateFieldInParentAndChild(): void
    {
        $metadata = new EncryptableMetadata();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('secretCode');

        $metadata->getEncryptableFieldNames(EncryptableDuplicateFieldEntityStub::class);
    }

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

    public function testGetHashNormalizationsForFieldSucceedsForPrivateFieldOfParentClass(): void
    {
        $metadata = new EncryptableMetadata();

        $result = $metadata->getHashNormalizationsForField(EncryptableProxyEntityStub::class, 'email');

        self::assertSame([HashNormalization::Lowercase], $result);
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
