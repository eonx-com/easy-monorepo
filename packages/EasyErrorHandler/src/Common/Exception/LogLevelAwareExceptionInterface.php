<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\Exception;

interface LogLevelAwareExceptionInterface
{
    public function getLogLevel(): int;
}
