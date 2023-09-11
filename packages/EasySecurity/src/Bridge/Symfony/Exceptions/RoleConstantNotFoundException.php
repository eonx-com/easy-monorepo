<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Exceptions;

use EonX\EasySecurity\Interfaces\EasySecurityExceptionInterface;
use RuntimeException;

final class RoleConstantNotFoundException extends RuntimeException implements EasySecurityExceptionInterface
{
    // No body needed
}
