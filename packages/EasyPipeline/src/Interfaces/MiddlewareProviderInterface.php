<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Interfaces;

interface MiddlewareProviderInterface
{
    /**
     * Get middleware list, middleware could be anything your container can resolve.
     *
     * @return mixed[]
     */
    public function getMiddlewareList(): array;
}

\class_alias(
    MiddlewareProviderInterface::class,
    'LoyaltyCorp\EasyPipeline\Interfaces\MiddlewareProviderInterface',
    false
);
