<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

use Monolog\Level;

interface LogLevelAwareExceptionInterface
{
    public function getLogLevel(): Level;
}
