<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Provider;

interface MiddlewareProviderInterface
{
    public function getMiddlewareList(): array;
}
