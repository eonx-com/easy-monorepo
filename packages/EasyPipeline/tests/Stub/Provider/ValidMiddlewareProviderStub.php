<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Stub\Provider;

use EonX\EasyPipeline\Provider\MiddlewareProviderInterface;

final class ValidMiddlewareProviderStub implements MiddlewareProviderInterface
{
    public function getMiddlewareList(): array
    {
        return ['middleware1', 'middleware2'];
    }
}
