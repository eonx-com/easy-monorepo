<?php
declare(strict_types=1);

namespace EonX\EasyLogging\Interfaces;

interface ExceptionContextModifierInterface
{
    /**
     * Modify given context based on given throwable.
     *
     * @param \Throwable $throwable
     * @param mixed[] $context
     *
     * @return mixed[]
     */
    public function modifyContext(\Throwable $throwable, array $context): array;
}
