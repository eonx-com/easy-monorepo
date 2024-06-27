<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use EonX\EasyUtils\Common\Helper\HasPriorityInterface;
use Throwable;

interface ErrorReporterInterface extends HasPriorityInterface
{
    public function report(Throwable $throwable): void;
}
