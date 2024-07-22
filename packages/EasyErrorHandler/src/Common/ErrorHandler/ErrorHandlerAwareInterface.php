<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ErrorHandler;

interface ErrorHandlerAwareInterface
{
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void;
}
