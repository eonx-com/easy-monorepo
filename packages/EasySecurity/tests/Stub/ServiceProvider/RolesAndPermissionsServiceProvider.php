<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Stub\ServiceProvider;

use EonX\EasySecurity\Authorization\ValueObject\Permission;
use EonX\EasySecurity\Authorization\ValueObject\Role;
use EonX\EasySecurity\Bundle\Enum\ConfigTag;
use EonX\EasySecurity\Tests\Stub\Provider\RolesAndPermissionsProviderStub;
use Illuminate\Support\ServiceProvider;

final class RolesAndPermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            RolesAndPermissionsProviderStub::class,
            static fn (): RolesAndPermissionsProviderStub => new RolesAndPermissionsProviderStub(
                [new Role('role')],
                [new Permission('permission')]
            )
        );

        $this->app->tag([RolesAndPermissionsProviderStub::class], [
            ConfigTag::RolesProvider->value,
            ConfigTag::PermissionsProvider->value,
        ]);
    }
}
