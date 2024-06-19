<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Common\Exception;

use Exception;

abstract class AbstractEasyApiTokenFactoryException extends Exception implements EasyApiTokenExceptionInterface
{
    // No body needed
}
