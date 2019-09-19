<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Exceptions;

use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceExceptionInterface;

final class NoIdentityUserIdException extends \RuntimeException implements IdentityServiceExceptionInterface
{
    // No body needed.
}

\class_alias(
    NoIdentityUserIdException::class,
    \StepTheFkUp\EasyIdentity\Exceptions\NoIdentityUserIdException::class,
    false
);
