<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use StepTheFkUp\EasyPipeline\Interfaces\MiddlewareProviderInterface;

final class ValidMiddlewareProviderStub implements MiddlewareProviderInterface
{
    /**
     * Get middleware list, middleware could be anything your container can resolve.
     *
     * @return mixed[]
     */
    public function getMiddlewareList(): array
    {
        return ['middleware1', 'middleware2'];
    }
}

\class_alias(
    ValidMiddlewareProviderStub::class,
    'LoyaltyCorp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\ValidMiddlewareProviderStub',
    false
);
