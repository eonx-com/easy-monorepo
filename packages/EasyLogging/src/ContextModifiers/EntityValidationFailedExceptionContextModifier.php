<?php

declare(strict_types=1);

namespace EonX\EasyLogging\ContextModifiers;

use App\Exceptions\EntityValidationFailedException;
use EonX\EasyLogging\Interfaces\ExceptionContextModifierInterface;

/**
 * @deprecated since 2.4, will be removed in 3.0. Use Monolog\Processor\ProcessorInterface instead.
 */
final class EntityValidationFailedExceptionContextModifier implements ExceptionContextModifierInterface
{
    /**
     * @param mixed[] $context
     *
     * @return mixed[]
     */
    public function modifyContext(\Throwable $throwable, array $context): array
    {
        if ($throwable instanceof EntityValidationFailedException) {
            $context['errors'] = $throwable->getErrors();
        }

        return $context;
    }
}
