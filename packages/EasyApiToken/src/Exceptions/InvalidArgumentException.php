<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Exceptions;

final class InvalidArgumentException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    InvalidArgumentException::class,
    'LoyaltyCorp\EasyApiToken\Exceptions\InvalidArgumentException',
    false
);
