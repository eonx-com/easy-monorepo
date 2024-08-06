<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Unit\SymfonySecurity\Voter;

use EonX\EasySecurity\SymfonySecurity\Voter\PermissionVoter;
use EonX\EasySecurity\SymfonySecurity\Voter\ProviderVoter;
use EonX\EasySecurity\SymfonySecurity\Voter\RoleVoter;
use EonX\EasySecurity\Tests\Unit\AbstractSymfonyTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class EnableVoterTest extends AbstractSymfonyTestCase
{
    /**
     * @see testVotersEnabled
     */
    public static function provideVotersEnabledData(): iterable
    {
        yield 'All disabled by default' => [
            [],
            [
                PermissionVoter::class => false,
                ProviderVoter::class => false,
                RoleVoter::class => false,
            ],
        ];

        yield 'Permission only implicit' => [
            [__DIR__ . '/../../../../Fixture/config/voters_permission_only_implicit.php'],
            [
                PermissionVoter::class => true,
                ProviderVoter::class => false,
                RoleVoter::class => false,
            ],
        ];

        yield 'Provider only implicit' => [
            [__DIR__ . '/../../../../Fixture/config/voters_provider_only_implicit.php'],
            [
                PermissionVoter::class => false,
                ProviderVoter::class => true,
                RoleVoter::class => false,
            ],
        ];

        yield 'Role only implicit' => [
            [__DIR__ . '/../../../../Fixture/config/voters_role_only_implicit.php'],
            [
                PermissionVoter::class => false,
                ProviderVoter::class => false,
                RoleVoter::class => true,
            ],
        ];

        yield 'All enabled explicit' => [
            [__DIR__ . '/../../../../Fixture/config/voters_all_enabled_explicit.php'],
            [
                PermissionVoter::class => true,
                ProviderVoter::class => true,
                RoleVoter::class => true,
            ],
        ];
    }

    /**
     * @param string[] $configs
     */
    #[DataProvider('provideVotersEnabledData')]
    public function testVotersEnabled(array $configs, array $assertions): void
    {
        $container = $this->getKernel($configs)
            ->getContainer();

        foreach ($assertions as $id => $bool) {
            self::assertEquals($bool, $container->has($id));
        }
    }
}
