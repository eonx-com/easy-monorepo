<?php

declare(strict_types=1);

namespace EonX\EasySecurity;

use EonX\EasySecurity\Interfaces\ContextInterface;

/**
 * @deprecated Since 2.4, will be removed in 3.0. Use SecurityContext instead.
 */
final class Context extends SecurityContext implements ContextInterface
{
    // No body needed.
}
