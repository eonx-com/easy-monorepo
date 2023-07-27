<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Repositories;

final class BatchCountsDto
{
    public function __construct(
        private readonly int $countCancelled,
        private readonly int $countFailed,
        private readonly int $countProcessed,
        private readonly int $countSucceeded,
        private readonly int $countTotal,
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
