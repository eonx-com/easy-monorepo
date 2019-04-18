<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Exceptions;

final class UnableToEncodeEasyApiTokenException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    UnableToEncodeEasyApiTokenException::class,
    'LoyaltyCorp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException',
    false
);
