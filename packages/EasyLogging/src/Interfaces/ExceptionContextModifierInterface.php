<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

interface ExceptionContextModifierInterface
{
    /**
     * @param mixed[] $context
     *
     * @return mixed[]
     */
    public function modifyContext(\Throwable $throwable, array $context): array;
}
