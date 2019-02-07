<?php
declare(strict_types=1);

namespace StepTheFkUp\Pagination\Interfaces;

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
