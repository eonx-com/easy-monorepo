<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Exceptions;

use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceExceptionInterface;

final class InvalidResponseFromIdentityException extends \RuntimeException implements IdentityServiceExceptionInterface
{
    // No body needed.
}

\class_alias(
    InvalidResponseFromIdentityException::class,
    \StepTheFkUp\EasyIdentity\Exceptions\InvalidResponseFromIdentityException::class,
    false
);
