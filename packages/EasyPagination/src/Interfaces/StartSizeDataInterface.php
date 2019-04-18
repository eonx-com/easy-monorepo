<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPagination\Interfaces;

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
    'LoyaltyCorp\EasyPagination\Interfaces\StartSizeDataInterface',
    false
);
