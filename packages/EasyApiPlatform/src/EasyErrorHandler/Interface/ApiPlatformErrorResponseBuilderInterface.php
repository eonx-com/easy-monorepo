<?php
declare(strict_types=1);

namespace EonX\EasyApiPlatform\EasyErrorHandler\Interface;

use EonX\EasyErrorHandler\Common\Builder\ErrorResponseBuilderInterface;
use Throwable;

interface ApiPlatformErrorResponseBuilderInterface extends ErrorResponseBuilderInterface
{
    public function supports(Throwable $throwable): bool;
}
