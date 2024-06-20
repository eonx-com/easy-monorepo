<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\ErrorCodes\Processor;

use EonX\EasyErrorHandler\ErrorCodes\ValueObject\ErrorCodes;

interface ErrorCodesGroupProcessorInterface
{
    public function process(): ErrorCodes;
}
