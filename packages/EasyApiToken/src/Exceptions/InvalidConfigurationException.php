<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Exceptions;

final class InvalidConfigurationException extends AbstractEasyApiTokenException
{
    // No body needed.
}

\class_alias(
    InvalidConfigurationException::class,
    'StepTheFkUp\EasyApiToken\Exceptions\InvalidConfigurationException',
    false
);
