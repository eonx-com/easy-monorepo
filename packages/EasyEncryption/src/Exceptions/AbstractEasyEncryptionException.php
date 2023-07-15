<?php

declare(strict_types=1);

namespace EonX\EasyEncryption\Exceptions;

use EonX\EasyEncryption\Interfaces\EasyEncryptionExceptionInterface;
use RuntimeException;

abstract class AbstractEasyEncryptionException extends RuntimeException implements EasyEncryptionExceptionInterface
{
    // No body needed
}
