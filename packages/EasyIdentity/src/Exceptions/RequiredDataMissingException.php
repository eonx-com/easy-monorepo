<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyIdentity\Exceptions;

use LoyaltyCorp\EasyIdentity\Interfaces\IdentityServiceExceptionInterface;

class RequiredDataMissingException extends \RuntimeException implements IdentityServiceExceptionInterface
{
    // No body needed.
}

\class_alias(
    RequiredDataMissingException::class,
    'StepTheFkUp\EasyIdentity\Exceptions\RequiredDataMissingException',
    false
);
