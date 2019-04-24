<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Exceptions;

final class InvalidEasyApiTokenFromRequestException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    InvalidEasyApiTokenFromRequestException::class,
    'LoyaltyCorp\EasyApiToken\Exceptions\InvalidEasyApiTokenFromRequestException',
    false
);
