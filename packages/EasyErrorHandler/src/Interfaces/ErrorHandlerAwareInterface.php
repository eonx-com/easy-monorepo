<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Interfaces;

interface ErrorHandlerAwareInterface
{
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void;
}
