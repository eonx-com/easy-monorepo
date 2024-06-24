<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

final readonly class BatchCountsDto
{
    public function __construct(
        private int $countCancelled,
        private int $countFailed,
        private int $countProcessed,
        private int $countSucceeded,
        private int $countTotal,
    ) {
    }

    public function countCancelled(): int
    {
        return $this->countCancelled;
    }

    public function countFailed(): int
    {
        return $this->countFailed;
    }

    public function countProcessed(): int
    {
        return $this->countProcessed;
    }

    public function countSucceeded(): int
    {
        return $this->countSucceeded;
    }

    public function countTotal(): int
    {
        return $this->countTotal;
    }
}
