<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Stubs;

use EonX\EasySecurity\Bridge\Symfony\Interfaces\AuthenticationExceptionInterface;
use Exception;

final class CustomAuthenticationException extends Exception implements AuthenticationExceptionInterface
{
    // The body is not required
}
