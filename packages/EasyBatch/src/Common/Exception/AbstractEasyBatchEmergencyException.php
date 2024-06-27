<?php
declare(strict_types=1);

namespace EonX\EasyBatch\Common\Exception;

use EonX\EasyBatch\Common\Exception\EasyBatchEmergencyExceptionInterface as EmergencyInterface;

abstract class AbstractEasyBatchEmergencyException extends AbstractEasyBatchException implements EmergencyInterface
{
}
