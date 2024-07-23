<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Exception;

interface EasyBatchExceptionInterface
{
    public function shouldRetry(): bool;
}
