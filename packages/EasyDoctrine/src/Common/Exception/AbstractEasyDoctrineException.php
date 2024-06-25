<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Common\Exception;

use RuntimeException;

abstract class AbstractEasyDoctrineException extends RuntimeException implements EasyDoctrineExceptionInterface
{
    // No body needed
}
