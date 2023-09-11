<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces\Exceptions;

interface LogLevelAwareExceptionInterface
{
    public function getLogLevel(): int;
}
