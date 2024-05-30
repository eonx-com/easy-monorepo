<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\Bridge\EasyErrorHandler\Interface;

use EonX\EasyErrorHandler\Interfaces\ErrorResponseBuilderInterface;
use Throwable;

interface ApiPlatformErrorResponseBuilderInterface extends ErrorResponseBuilderInterface
{
    public function supports(Throwable $throwable): bool;
}
