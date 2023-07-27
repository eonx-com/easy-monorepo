<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

use Throwable;

interface EasyBatchEmergencyExceptionInterface extends EasyBatchExceptionInterface
{
    public function getPrevious(): ?Throwable;
}
