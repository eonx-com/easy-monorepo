<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\Exception;

use EonX\EasySecurity\SymfonySecurity\Exception\AuthenticationExceptionInterface;
use Exception;

final class CustomAuthenticationException extends Exception implements AuthenticationExceptionInterface
{
    // The body is not required
}
