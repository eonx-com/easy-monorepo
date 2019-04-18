<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyIdentity\Exceptions;

use StepTheFkUp\EasyIdentity\Interfaces\IdentityServiceExceptionInterface;

class RequiredDataMissingException extends \RuntimeException implements IdentityServiceExceptionInterface
{
    // No body needed.
}

\class_alias(
    RequiredDataMissingException::class,
    'LoyaltyCorp\EasyIdentity\Exceptions\RequiredDataMissingException',
    false
);
