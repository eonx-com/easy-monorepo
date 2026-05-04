<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\EasyEncryption\Serializer;

use EonX\EasyActivity\Common\Enum\ActivityAction;
use EonX\EasyActivity\Common\Resolver\DefaultActorResolver;
use EonX\EasyActivity\Tests\Fixture\App\Entity\ActivityLog;
use EonX\EasyActivity\Tests\Fixture\App\Entity\User;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;

final class ActivityLogEncryptableFieldsMaskingSerializerTest extends AbstractUnitTestCase
{
    private bool $appSecretEnvModified = false;

    protected function tearDown(): void
    {
        if ($this->appSecretEnvModified) {
            \putenv('APP_SECRET');
            unset($_ENV['APP_SECRET']);
        }
        parent::tearDown();
    }

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/encryptable_fields_integration
     */
    public function testEncryptableFieldsAreMaskedOnCreate(): void
    {
        $this->setUpEncryptionEnvironment();
        $entityManager = self::getEntityManager();
        $user = (new User())
            ->setUsername('john_doe')
            ->setPassword('secret_password')
            ->setEmail('john@example.com')
            ->setCreditCard('1234-5678-9012-3456')
            ->setSsn('123-45-6789');
        $entityManager->persist($user);
        $userId = $user->getId();

        $entityManager->flush();

        self::assertEntityCount(ActivityLog::class, 1);
        self::assertEntityExists(ActivityLog::class, [
            'action' => ActivityAction::Create->value,
            'actorId' => null,
            'actorName' => null,
            'actorType' => DefaultActorResolver::DEFAULT_ACTOR_TYPE,
            'subjectId' => $userId,
            'subjectType' => 'User',
            'subjectData' => \json_encode([
                'ssn' => '*ENCRYPTED*',
                'creditCard' => '*ENCRYPTED*',
                'id' => $userId,
                'username' => 'john_doe',
            ]),
            'subjectOldData' => null,
        ]);
    }

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/encryptable_fields_integration
     */
    public function testEncryptableFieldsAreMaskedOnUpdate(): void
    {
        $this->setUpEncryptionEnvironment();
        $entityManager = self::getEntityManager();
        $user = (new User())
            ->setUsername('jane_doe')
            ->setPassword('initial_password')
            ->setEmail('jane@example.com')
            ->setCreditCard('1111-2222-3333-4444')
            ->setSsn('111-22-3333');
        $entityManager->persist($user);
        $userId = $user->getId();
        $entityManager->flush();
        $user->setCreditCard('5555-6666-7777-8888');
        $user->setUsername('jane_doe_updated');

        $entityManager->flush();

        self::assertEntityCount(ActivityLog::class, 2);
        self::assertEntityExists(ActivityLog::class, [
            'action' => ActivityAction::Update->value,
            'actorId' => null,
            'actorName' => null,
            'actorType' => DefaultActorResolver::DEFAULT_ACTOR_TYPE,
            'subjectId' => $userId,
            'subjectType' => 'User',
            'subjectData' => \json_encode([
                'creditCard' => '*ENCRYPTED*',
                'username' => 'jane_doe_updated',
            ]),
            'subjectOldData' => \json_encode([
                'creditCard' => '*ENCRYPTED*',
                'username' => 'jane_doe',
            ]),
        ]);
    }

    /**
     * @see packages/EasyActivity/tests/Fixture/app/config/packages/no_encryption_bundle
     */
    public function testNoMaskingWhenEncryptionBundleNotEnabled(): void
    {
        self::bootKernel(['environment' => 'no_encryption_bundle']);
        $this->initDatabase();
        $entityManager = self::getEntityManager();
        $user = (new User())
            ->setUsername('test_user')
            ->setPassword('password123')
            ->setEmail('test@example.com')
            ->setCreditCard('9999-8888-7777-6666')
            ->setSsn('999-88-7777');
        $entityManager->persist($user);
        $userId = $user->getId();

        $entityManager->flush();

        self::assertEntityCount(ActivityLog::class, 1);
        self::assertEntityExists(ActivityLog::class, [
            'action' => ActivityAction::Create->value,
            'actorId' => null,
            'actorName' => null,
            'actorType' => DefaultActorResolver::DEFAULT_ACTOR_TYPE,
            'subjectId' => $userId,
            'subjectType' => 'User',
            'subjectData' => \json_encode([
                'ssn' => '999-88-7777',
                'creditCard' => '9999-8888-7777-6666',
                'id' => $userId,
                'username' => 'test_user',
            ]),
            'subjectOldData' => null,
        ]);
    }

    private function setUpEncryptionEnvironment(): void
    {
        if (\getenv('APP_SECRET') === false) {
            \putenv('APP_SECRET=test-secret-for-encryption-testing');
            $_ENV['APP_SECRET'] = 'test-secret-for-encryption-testing';
            $this->appSecretEnvModified = true;
        }
        self::bootKernel(['environment' => 'encryptable_fields_integration']);
        $this->initDatabase();
    }
}
