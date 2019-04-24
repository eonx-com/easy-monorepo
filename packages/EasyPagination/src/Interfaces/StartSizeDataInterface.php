<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyPagination\Interfaces;

interface StartSizeDataInterface
{
    /**
     * Get start.
     *
     * @return int
     */
    public function getStart(): int;

    /**
     * Get size.
     *
     * @return int
     */
    public function getSize(): int;
}

\class_alias(
    StartSizeDataInterface::class,
    'StepTheFkUp\EasyPagination\Interfaces\StartSizeDataInterface',
    false
);
