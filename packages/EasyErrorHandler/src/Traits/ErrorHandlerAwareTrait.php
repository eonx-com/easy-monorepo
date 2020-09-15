<?php

declare(strict_types=1);

namespace EonX\EasyErrorHandler\Traits;

use EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface;

trait ErrorHandlerAwareTrait
{
    /**
     * @var \EonX\EasyErrorHandler\Interfaces\ErrorHandlerInterface
     */
    protected $errorHandler;

    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }
}
