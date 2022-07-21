<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

use EonX\EasyErrorHandler\DataTransferObjects\ErrorCodesDto;

interface ErrorCodesGroupProcessorInterface
{
    public function process(): ErrorCodesDto;
}
