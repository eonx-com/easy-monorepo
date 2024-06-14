<?php
declare(strict_types=1);

namespace EonX\EasyErrorHandler\Bridge\Symfony\Interfaces;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface;
use Throwable;

interface ApiPlatformErrorResponseBuilderInterface extends ErrorResponseBuilderInterface
{
    public function supports(Throwable $throwable): bool;
}
