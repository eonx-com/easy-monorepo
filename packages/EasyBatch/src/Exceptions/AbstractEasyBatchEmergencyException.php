<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Exceptions;

use EonX\EasyBatch\Interfaces\EasyBatchEmergencyExceptionInterface as EmergencyInterface;

abstract class AbstractEasyBatchEmergencyException extends AbstractEasyBatchException implements EmergencyInterface
{
}
