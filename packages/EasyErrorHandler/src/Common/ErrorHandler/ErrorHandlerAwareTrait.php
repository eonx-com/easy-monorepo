<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Common\ErrorHandler;

use Symfony\Contracts\Service\Attribute\Required;

trait ErrorHandlerAwareTrait
{
    protected ErrorHandlerInterface $errorHandler;

    #[Required]
    public function setErrorHandler(ErrorHandlerInterface $errorHandler): void
    {
        $this->errorHandler = $errorHandler;
    }
}
