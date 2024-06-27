<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Exception;

use Throwable;

interface EasyBatchEmergencyExceptionInterface extends EasyBatchExceptionInterface
{
    public function getPrevious(): ?Throwable;
}
