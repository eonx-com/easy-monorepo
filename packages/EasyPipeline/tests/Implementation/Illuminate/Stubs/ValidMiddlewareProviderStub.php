<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

use EonX\EasyPipeline\Interfaces\MiddlewareProviderInterface;

final class ValidMiddlewareProviderStub implements MiddlewareProviderInterface
{
    public function getMiddlewareList(): array
    {
        return ['middleware1', 'middleware2'];
    }
}
