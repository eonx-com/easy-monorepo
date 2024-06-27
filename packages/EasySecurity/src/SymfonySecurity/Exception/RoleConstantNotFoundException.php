<?php
declare(strict_types=1);

namespace EonX\EasySecurity\SymfonySecurity\Exception;

use EonX\EasySecurity\Common\Exception\EasySecurityExceptionInterface;
use RuntimeException;

final class RoleConstantNotFoundException extends RuntimeException implements EasySecurityExceptionInterface
{
    // No body needed
}
