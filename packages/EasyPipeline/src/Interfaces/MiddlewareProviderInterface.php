<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface MiddlewareProviderInterface
{
    public function getMiddlewareList(): array;
}
