<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\ServiceProvider;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

final class FailingBootServiceProviderStub extends ServiceProvider
{
    public const EXCEPTION_MESSAGE = 'Provider cannot boot.';

    public function boot(): never
    {
        throw new RuntimeException(self::EXCEPTION_MESSAGE);
    }
}
