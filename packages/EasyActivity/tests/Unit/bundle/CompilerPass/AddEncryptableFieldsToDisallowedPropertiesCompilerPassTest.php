<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Tests\Unit\Bundle\CompilerPass;

use EonX\EasyActivity\Bundle\Enum\ConfigParam as EasyActivityConfigParam;
use EonX\EasyActivity\Tests\Fixture\App\Entity\User;
use EonX\EasyActivity\Tests\Unit\AbstractUnitTestCase;

final class AddEncryptableFieldsToDisallowedPropertiesCompilerPassTest extends AbstractUnitTestCase
{
    public function testItSucceedsAndEncryptableFieldsAreAutomaticallyAddedToDisallowedProperties(): void
    {
        self::bootKernel(['environment' => 'encryptable_fields_integration']);

        /** @var array<class-string, array> $subjects */
        $subjects = self::getContainer()->getParameter(EasyActivityConfigParam::Subjects->value);

        self::assertSame(['password', 'creditCard', 'ssn'], $subjects[User::class]['disallowed_properties']);
    }

    public function testItSucceedsWhenEncryptionBundleNotAvailable(): void
    {
        self::bootKernel(['environment' => 'no_encryption_bundle']);

        /** @var array<class-string, array> $subjects */
        $subjects = self::getContainer()->getParameter(EasyActivityConfigParam::Subjects->value);

        // When EasyEncryptionBundle is not loaded, only manually configured properties should be disallowed
        // The compiler pass should early-return and NOT add encryptable fields (creditCard, ssn)
        self::assertSame(['password'], $subjects[User::class]['disallowed_properties']);
    }
}
