<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyApiToken\Exceptions;

final class EmptyRequiredPayloadException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    EmptyRequiredPayloadException::class,
    'LoyaltyCorp\EasyApiToken\Exceptions\EmptyRequiredPayloadException',
    false
);
