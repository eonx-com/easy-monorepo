<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Fixture\ClassMap;

use EonX\EasyServerless\Tests\Stub\SnapStart\Hook\AfterRestoreHookStub;

final class SignatureClassMap
{
    public function createAfterRestoreHook(): AfterRestoreHookStub
    {
        return new AfterRestoreHookStub();
    }
}
