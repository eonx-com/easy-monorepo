<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Tests\Unit\Encryptable\Encryptable;

use EonX\EasyEncryption\Encryptable\HashCalculator\HmacSha512HashCalculator;
use EonX\EasyEncryption\Encryptable\Hasher\EncryptableFieldHasher;
use EonX\EasyEncryption\Encryptable\Metadata\EncryptableMetadata;
use EonX\EasyEncryption\Encryptable\Normalizer\HashNormalizer;
use EonX\EasyEncryption\Encryptable\ValueObject\EncryptedString;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableChildEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableEntityStub;
use EonX\EasyEncryption\Tests\Stub\Entity\EncryptableProxyEntityStub;
use EonX\EasyEncryption\Tests\Unit\AbstractUnitTestCase;

final class EncryptableTraitEncryptableTest extends AbstractUnitTestCase
{
    public function testEncryptSucceedsWithComposedFieldOverride(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');
        // Global default is "no normalization"; only the field-level override should apply here
        $fieldHasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);
        $entity = new EncryptableEntityStub(username: '  Jane.Doe  ');

        $entity->encrypt(...$this->closures($fieldHasher));

        /** @var array<string, string|null> $rawData */
        $rawData = \json_decode($entity->getEncryptedData(), true);
        self::assertSame('  Jane.Doe  ', $rawData['username']);
        self::assertSame($calculator->calculate('jane.doe'), $entity->getUsername());
    }

    public function testEncryptSucceedsWithLowercaseDefault(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');
        $fieldHasher = new EncryptableFieldHasher(
            $calculator,
            new EncryptableMetadata(),
            new HashNormalizer(),
            ['lowercase']
        );
        $entity = new EncryptableEntityStub(email: 'John.Doe@Example.com');

        $entity->encrypt(...$this->closures($fieldHasher));

        /** @var array<string, string|null> $rawData */
        $rawData = \json_decode($entity->getEncryptedData(), true);
        self::assertSame('John.Doe@Example.com', $rawData['email']);
        self::assertSame($calculator->calculate('john.doe@example.com'), $entity->getEmail());
    }

    public function testEncryptSucceedsWithPrivateFieldOfParentClassAndTraitInChild(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');
        $fieldHasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);
        $entity = new EncryptableChildEntityStub(email: 'jane@example.com', secretCode: 'Code-123');

        $entity->encrypt(...$this->closures($fieldHasher));

        /** @var array<string, string|null> $rawData */
        $rawData = \json_decode($entity->getEncryptedData(), true);
        self::assertSame(
            [
                'email' => 'jane@example.com',
                'secret_code' => 'Code-123',
            ],
            $rawData
        );
        self::assertSame($calculator->calculate('Code-123'), $entity->getSecretCode());
        self::assertSame($calculator->calculate('jane@example.com'), $entity->getEmail());

        $entity->decrypt(static fn(string $value): string => $value);

        self::assertSame('Code-123', $entity->getSecretCode());
        self::assertSame('jane@example.com', $entity->getEmail());
    }

    public function testEncryptSucceedsWithPrivateFieldOfParentClassOnProxyLikeSubclass(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');
        $fieldHasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);
        $entity = new EncryptableProxyEntityStub(email: 'Jane@Example.com');

        $entity->encrypt(...$this->closures($fieldHasher));

        /** @var array<string, string|null> $rawData */
        $rawData = \json_decode($entity->getEncryptedData(), true);
        self::assertSame(['email' => 'Jane@Example.com'], $rawData);
        self::assertSame($calculator->calculate('jane@example.com'), $entity->getEmail());

        $entity->decrypt(static fn(string $value): string => $value);

        self::assertSame('Jane@Example.com', $entity->getEmail());
    }

    public function testEncryptSucceedsWithSameHashAsReadPathUsingFieldOverride(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');
        $fieldHasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);
        $entity = new EncryptableEntityStub(username: 'Jane.Doe');

        $entity->encrypt(...$this->closures($fieldHasher));
        // Simulate a repository hashing a differently-cased search term for a lookup query
        $lookupHash = $fieldHasher->hashForField(EncryptableEntityStub::class, 'username', '  jane.doe  ');

        self::assertSame($lookupHash, $entity->getUsername());
    }

    public function testEncryptSucceedsWithSameHashAsReadPathUsingGlobalDefault(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');
        $fieldHasher = new EncryptableFieldHasher(
            $calculator,
            new EncryptableMetadata(),
            new HashNormalizer(),
            ['lowercase']
        );
        $entity = new EncryptableEntityStub(email: 'John.Doe@Example.com');

        $entity->encrypt(...$this->closures($fieldHasher));
        // Simulate a repository hashing a differently-cased search term for a lookup query
        $lookupHash = $fieldHasher->hashForField(EncryptableEntityStub::class, 'email', 'john.doe@example.com');

        self::assertSame($lookupHash, $entity->getEmail());
    }

    public function testEncryptSucceedsWithoutNormalizationDefault(): void
    {
        $calculator = new HmacSha512HashCalculator('secret');
        $fieldHasher = new EncryptableFieldHasher($calculator, new EncryptableMetadata(), new HashNormalizer(), []);
        $entity = new EncryptableEntityStub(email: 'John.Doe@Example.com');

        $entity->encrypt(...$this->closures($fieldHasher));

        /** @var array<string, string|null> $rawData */
        $rawData = \json_decode($entity->getEncryptedData(), true);
        self::assertSame('John.Doe@Example.com', $rawData['email']);
        self::assertSame($calculator->calculate('John.Doe@Example.com'), $entity->getEmail());
    }

    /**
     * @return array{0: callable(string): \EonX\EasyEncryption\Encryptable\ValueObject\EncryptedString, 1: callable(string, string, string): string}
     */
    private function closures(EncryptableFieldHasher $fieldHasher): array
    {
        return [
            static fn(string $value): EncryptedString => new EncryptedString('test-key', $value),
            static function (string $entityClass, string $propertyName, string $value) use ($fieldHasher): string {
                /** @var class-string $entityClass */
                return $fieldHasher->hashForField($entityClass, $propertyName, $value);
            },
        ];
    }
}
