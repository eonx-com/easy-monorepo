<?php

declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Laravel\Fixtures\Providers;

use EonX\EasySecurity\Authorization\Permission;
use EonX\EasySecurity\Authorization\Role;
use EonX\EasySecurity\Bridge\TagsInterface;
use EonX\EasySecurity\Tests\Stubs\RolesAndPermissionsProviderStub;
use Illuminate\Support\ServiceProvider;

final class RolesAndPermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RolesAndPermissionsProviderStub::class,
            static function (): RolesAndPermissionsProviderStub {
                return new RolesAndPermissionsProviderStub([new Role('role')], [new Permission('permission')]);
            }
        );

        $this->app->tag([RolesAndPermissionsProviderStub::class], [
            TagsInterface::TAG_ROLES_PROVIDER,
            TagsInterface::TAG_PERMISSIONS_PROVIDER,
        ]);
    }
}
