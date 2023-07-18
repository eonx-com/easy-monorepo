<?php

declare(strict_types=1);

namespace EonX\EasyBankFiles\Exceptions;

/**
 * An exception for a situation that should never happen (null checks when it is known
 * that it will never be null, etc).
 */
final class ImpossibleException extends AbstractRuntimeException
{
    // No body needed
}
