<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Tests\Implementations\Stubs;

use LoyaltyCorp\EasyIdentity\Interfaces\IdentityUserInterface;

/**
 * @noinspection EmptyClassInspection Stub is required for testing of the user service.
 */
final class IdentityUserStub implements IdentityUserInterface
{
}

\class_alias(
    IdentityUserStub::class,
    StepTheFkUp\EasyIdentity\Tests\Implementations\Stubs\IdentityUserStub::class,
    false
);
