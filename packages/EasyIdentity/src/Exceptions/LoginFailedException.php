<?php

declare(strict_types=1);

namespace EonX\EasyIdentity\Exceptions;

use EonX\EasyIdentity\Interfaces\IdentityServiceExceptionInterface;

class LoginFailedException extends \RuntimeException implements IdentityServiceExceptionInterface
{
    // No body needed.
}
