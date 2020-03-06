<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\RolesProviders;

use EonX\EasySecurity\RolesProviders\AbstractInMemoryRolesProvider;

final class InMemoryRolesProviderStub extends AbstractInMemoryRolesProvider
{
    /**
     * @var \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    private $rolesToInit;

    /**
     * @param null|\EonX\EasySecurity\Interfaces\RoleInterface[] $rolesToInit
     */
    public function __construct(?array $rolesToInit = null)
    {
        $this->rolesToInit = $rolesToInit ?? [];
    }

    /**
     * @return \EonX\EasySecurity\Interfaces\RoleInterface[]
     */
    protected function initRoles(): array
    {
        return $this->rolesToInit;
    }
}
