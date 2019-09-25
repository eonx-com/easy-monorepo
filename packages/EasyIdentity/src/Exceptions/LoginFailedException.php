<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Exceptions;

use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceExceptionInterface;

class LoginFailedException extends \RuntimeException implements IdentityServiceExceptionInterface
{
    // No body needed.
}

\class_alias(
    LoginFailedException::class,
    \StepTheFkUp\EasyIdentity\Exceptions\LoginFailedException::class,
    false
);
