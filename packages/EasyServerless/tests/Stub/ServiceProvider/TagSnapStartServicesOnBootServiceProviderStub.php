<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\ServiceProvider;

use EonX\EasyServerless\SnapStart\Enum\SnapStartTag;
use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;
use EonX\EasyServerless\Tests\Stub\SnapStart\Warmer\SnapStartWarmerStub;
use Illuminate\Support\ServiceProvider;

final class TagSnapStartServicesOnBootServiceProviderStub extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->tag([SnapStartWarmerStub::class], [SnapStartTag::Warmer->value]);
        $this->app->tag([AfterRestoreHookStub::class], [SnapStartTag::AfterRestoreHook->value]);
    }
}
