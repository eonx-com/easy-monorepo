<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Interfaces;

interface MiddlewareProviderInterface
{
    /**
     * @return mixed[]
     */
    public function getMiddlewareList(): array;
}
