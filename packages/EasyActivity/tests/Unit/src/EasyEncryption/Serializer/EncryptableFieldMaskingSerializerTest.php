<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\EasyEncryption\Serializer;

use EonX\EasyActivity\Bundle\Enum\ConfigServiceId;
use EonX\EasyActivity\Common\Serializer\SymfonyActivitySubjectDataSerializer;
use EonX\EasyActivity\EasyEncryption\Serializer\EncryptableFieldMaskingSerializer;
use EonX\EasyActivity\Tests\Fixture\App\Entity\ActivityLogEntity;
use EonX\EasyActivity\Tests\Fixture\App\Entity\User;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Serializer\SerializerInterface;

final class EncryptableFieldMaskingSerializerTest extends AbstractUnitTestCase
{
    /**
     * @see testSerializeSucceeds
     */
    public static function provideSerializeData(): iterable
    {
        yield 'User entity with encrypted fields masked' => [
            'inputData' => [
                'id' => '123',
                'username' => 'john_doe',
                'creditCard' => '1234-5678-9012-3456',
                'ssn' => '123-45-6789',
                'email' => 'john@example.com',
            ],
            'expectedMaskedData' => [
                'id' => '123',
                'username' => 'john_doe',
                'creditCard' => '*ENCRYPTED*',
                'ssn' => '*ENCRYPTED*',
                'email' => 'john@example.com',
            ],
        ];

        yield 'User entity without encrypted fields in data' => [
            'inputData' => [
                'username' => 'jane_doe',
                'email' => 'jane@example.com',
            ],
            'expectedMaskedData' => [
                'username' => 'jane_doe',
                'email' => 'jane@example.com',
            ],
        ];

        yield 'User entity with only encrypted field present' => [
            'inputData' => [
                'creditCard' => '9999-8888-7777-6666',
            ],
            'expectedMaskedData' => [
                'creditCard' => '*ENCRYPTED*',
            ],
        ];
    }

    public function testSerializeSkipsMaskingWhenSubjectTypeIsNotAClass(): void
    {
        $decoratedSerializer = $this->createDecoratedSerializer();
        $subjects = [];
        $serializer = new EncryptableFieldMaskingSerializer($decoratedSerializer, $subjects);
        $subject = new ActivityLogEntity(
            id: '999',
            subjectType: 'custom_user_type',
            allowedProperties: []
        );
        $inputData = [
            'username' => 'bob',
            'creditCard' => '9999-8888-7777-6666',
        ];

        $result = $serializer->serialize($inputData, $subject, []);

        self::assertSame('{"username":"bob","creditCard":"9999-8888-7777-6666"}', $result);
    }

    #[DataProvider('provideSerializeData')]
    public function testSerializeSucceeds(array $inputData, array $expectedMaskedData): void
    {
        $decoratedSerializer = $this->createDecoratedSerializer();
        $subjects = [
            User::class => [
                'type' => 'User',
            ],
        ];
        $serializer = new EncryptableFieldMaskingSerializer($decoratedSerializer, $subjects);
        $subject = new ActivityLogEntity(
            id: 'some-id',
            subjectType: 'User',
            allowedProperties: []
        );

        $result = $serializer->serialize($inputData, $subject, []);

        self::assertNotNull($result);
        $decodedResult = \json_decode($result, true);
        self::assertIsArray($decodedResult);
        self::assertSame($expectedMaskedData, $decodedResult);
    }

    public function testSerializeSucceedsWhenSubjectNotInConfigButIsValidClass(): void
    {
        $decoratedSerializer = $this->createDecoratedSerializer();
        $subjects = [];
        $serializer = new EncryptableFieldMaskingSerializer($decoratedSerializer, $subjects);
        $subject = new ActivityLogEntity(
            id: '789',
            subjectType: User::class,
            allowedProperties: []
        );
        $inputData = [
            'username' => 'alice',
            'creditCard' => '1111-2222-3333-4444',
            'ssn' => '111-22-3333',
        ];

        $result = $serializer->serialize($inputData, $subject, []);

        self::assertSame('{"username":"alice","creditCard":"*ENCRYPTED*","ssn":"*ENCRYPTED*"}', $result);
    }

    private function createDecoratedSerializer(): SymfonyActivitySubjectDataSerializer
    {
        /** @var \EonX\EasyActivity\Common\CircularReferenceHandler\CircularReferenceHandlerInterface $circularReferenceHandler */
        $circularReferenceHandler = self::getService(ConfigServiceId::CircularReferenceHandler->value);

        return new SymfonyActivitySubjectDataSerializer(
            serializer: self::getService(SerializerInterface::class),
            circularReferenceHandler: $circularReferenceHandler,
            disallowedProperties: [],
            fullySerializableProperties: []
        );
    }
}
