<?php
declare(strict_types=1);

namespace EonX\EasyEncryption\Common\Exception;

use RuntimeException;

abstract class AbstractEasyEncryptionException extends RuntimeException implements EasyEncryptionExceptionInterface
{
    // No body needed
}
