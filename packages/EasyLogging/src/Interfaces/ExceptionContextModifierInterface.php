<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

/**
 * @deprecated since 2.4, will be removed in 3.0. Use Monolog\Processor\ProcessorInterface instead.
 */
interface ExceptionContextModifierInterface
{
    /**
     * @param mixed[] $context
     *
     * @return mixed[]
     */
    public function modifyContext(\Throwable $throwable, array $context): array;
}
