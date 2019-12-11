<?php
declare(strict_types=1);

namespace EonX\EasyIdentity\Exceptions;

use EonX\EasyIdentity\Interfaces\IdentityServiceExceptionInterface;

final class InvalidResponseFromIdentityException extends \RuntimeException implements IdentityServiceExceptionInterface
{
    // No body needed.
}


