<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\EasyEncryption\Serializer;

use EonX\EasyActivity\Common\Entity\ActivitySubject;
use EonX\EasyActivity\Common\Entity\ActivitySubjectInterface;
use EonX\EasyActivity\Common\Serializer\ActivitySubjectDataSerializerInterface;
use EonX\EasyActivity\Common\Serializer\EncryptedFieldMaskingSerializer;
use EonX\EasyActivity\Tests\Fixture\App\Entity\User;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class EncryptedFieldMaskingSerializerTest extends AbstractUnitTestCase
{
    /**
     * @see testSerialize
     */
    public static function provideSerializeData(): iterable
    {
        yield 'User entity with encrypted fields' => [
            'inputData' => [
                'id' => '123',
                'username' => 'john_doe',
                'password' => 'secret123',
                'creditCard' => '1234-5678-9012-3456',
                'ssn' => '123-45-6789',
                'email' => 'john@example.com',
            ],
            'expectedMaskedData' => [
                'id' => '123',
                'username' => 'john_doe',
                'password' => 'secret123', // Not encrypted in entity
                'creditCard' => '*ENCRYPTED*', // Has EncryptableField attribute
                'ssn' => '*ENCRYPTED*', // Has EncryptableField attribute (from UserBase)
                'email' => 'john@example.com',
            ],
            'subjectType' => User::class,
        ];

        yield 'User entity with only some fields present' => [
            'inputData' => [
                'username' => 'jane_doe',
                'email' => 'jane@example.com',
            ],
            'expectedMaskedData' => [
                'username' => 'jane_doe',
                'email' => 'jane@example.com',
            ],
            'subjectType' => User::class,
        ];

        yield 'User entity with encrypted field present' => [
            'inputData' => [
                'creditCard' => '9999-8888-7777-6666',
            ],
            'expectedMaskedData' => [
                'creditCard' => '*ENCRYPTED*',
            ],
            'subjectType' => User::class,
        ];

        yield 'User entity with custom short type name' => [
            'inputData' => [
                'id' => '123',
                'username' => 'john_doe',
                'creditCard' => '1234-5678-9012-3456',
                'ssn' => '123-45-6789',
            ],
            'expectedMaskedData' => [
                'id' => '123',
                'username' => 'john_doe',
                'creditCard' => '*ENCRYPTED*',
                'ssn' => '*ENCRYPTED*',
            ],
            'subjectType' => 'user',
        ];
    }

    #[DataProvider('provideSerializeData')]
    public function testSerialize(
        array $inputData,
        array $expectedMaskedData,
        string $subjectType,
    ): void {
        $decoratedSerializer = $this->createMock(ActivitySubjectDataSerializerInterface::class);
        $decoratedSerializer
            ->expects(self::once())
            ->method('serialize')
            ->with($expectedMaskedData, self::anything(), self::anything())
            ->willReturn('{"serialized": "data"}');
        $subjects = [
            User::class => [
                'type' => 'user', // Custom short name that maps to User::class
            ],
        ];
        $serializer = new EncryptedFieldMaskingSerializer($decoratedSerializer, $subjects);
        $subject = $this->createMock(ActivitySubjectInterface::class);
        $subject
            ->method('getActivitySubjectType')
            ->willReturn($subjectType);

        $result = $serializer->serialize($inputData, $subject, []);

        self::assertSame('{"serialized": "data"}', $result);
    }

    public function testSerializeMemorizesReflectionResults(): void
    {
        $decoratedSerializer = $this->createMock(ActivitySubjectDataSerializerInterface::class);
        $decoratedSerializer
            ->method('serialize')
            ->willReturn('{"serialized": "data"}');
        $subjects = [
            User::class => [],
        ];
        $serializer = new EncryptedFieldMaskingSerializer($decoratedSerializer, $subjects);
        $subject = new ActivitySubject(
            id: '123',
            type: User::class,
            disallowedProperties: [],
            nestedObjectAllowedProperties: [],
            allowedProperties: [],
            fullySerializableProperties: [],
        );
        $data = [
            'username' => 'john_doe',
            'creditCard' => '1234-5678-9012-3456',
            'ssn' => '123-45-6789',
        ];
        $cache = self::getPrivatePropertyValue($serializer, 'encryptableFieldsCache');
        self::assertIsArray($cache);
        self::assertSame([], $cache);

        $serializer->serialize($data, $subject, []);
        $cacheAfterFirstCall = self::getPrivatePropertyValue($serializer, 'encryptableFieldsCache');
        self::assertIsArray($cacheAfterFirstCall);
        self::assertCount(1, $cacheAfterFirstCall);
        self::assertArrayHasKey(User::class, $cacheAfterFirstCall);
    }
}
