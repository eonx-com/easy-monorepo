<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Exceptions;

final class UnableToEncodeEasyApiTokenException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    UnableToEncodeEasyApiTokenException::class,
    'StepTheFkUp\EasyApiToken\Exceptions\UnableToEncodeEasyApiTokenException',
    false
);
