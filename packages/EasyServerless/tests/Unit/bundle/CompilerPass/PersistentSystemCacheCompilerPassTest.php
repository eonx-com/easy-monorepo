<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Bundle\CompilerPass;

use EonX\EasyServerless\Bundle\CompilerPass\PersistentSystemCacheCompilerPass;
use EonX\EasyServerless\Bundle\EasyServerlessBundle;
use EonX\EasyServerless\Tests\Stub\Kernel\FrameworkKernelStub;
use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Cache\DependencyInjection\CachePoolPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\DependencyInjection\ResettableServicePass;

final class PersistentSystemCacheCompilerPassTest extends AbstractUnitTestCase
{
    private const CACHE_ITEM_KEY = 'key';

    private const PERSISTENT_CACHE_POOLS = [
        'cache.app',
        'cache.app_alias_child',
        'cache.app_child',
        'cache.app_grandchild',
        'cache.system',
        'cache.system_child',
        'cache.system_grandchild',
    ];

    private const RESET_CACHE_POOLS = [
        'cache.chain',
        'cache.standalone',
    ];

    private string|false $lambdaTaskRoot = false;

    private bool $serverHadSamLocal = false;

    private mixed $serverSamLocal = null;

    protected function setUp(): void
    {
        // Start outside any Lambda, tests opt in by setting LAMBDA_TASK_ROOT
        $this->lambdaTaskRoot = \getenv('LAMBDA_TASK_ROOT');
        \putenv('LAMBDA_TASK_ROOT');
        $this->serverHadSamLocal = \array_key_exists('AWS_SAM_LOCAL', $_SERVER);
        $this->serverSamLocal = $_SERVER['AWS_SAM_LOCAL'] ?? null;
        unset($_SERVER['AWS_SAM_LOCAL']);
    }

    protected function tearDown(): void
    {
        if ($this->lambdaTaskRoot === false) {
            \putenv('LAMBDA_TASK_ROOT');
        } else {
            \putenv('LAMBDA_TASK_ROOT=' . $this->lambdaTaskRoot);
        }

        if ($this->serverHadSamLocal) {
            $_SERVER['AWS_SAM_LOCAL'] = $this->serverSamLocal;
        }

        parent::tearDown();
    }

    public function testPassRunsBetweenCachePoolPassAndResettableServicePass(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.debug', false);
        // FrameworkBundle is registered first, as in applications
        (new FrameworkBundle())->build($container);
        $sut = new EasyServerlessBundle();
        // CachePoolPass adds the "kernel.reset" tags removed by the pass, and ResettableServicePass builds
        // "services_resetter" from the remaining ones
        $expectedPassOrder = [
            CachePoolPass::class,
            PersistentSystemCacheCompilerPass::class,
            ResettableServicePass::class,
        ];

        $sut->build($container);

        $passes = \array_map(
            static fn (CompilerPassInterface $pass): string => $pass::class,
            $container
                ->getCompilerPassConfig()
                ->getBeforeOptimizationPasses()
        );
        self::assertSame($expectedPassOrder, \array_values(\array_intersect($passes, $expectedPassOrder)));
    }

    public function testServicesResetterDoesNotResetSystemCachePoolsInLambda(): void
    {
        \putenv('LAMBDA_TASK_ROOT=/var/task');
        $kernel = new FrameworkKernelStub(
            'test',
            true,
            [__DIR__ . '/../../../Fixture/config/framework_array_cache.php']
        );
        $kernel->boot();
        $container = $kernel->getContainer();
        foreach ([...self::PERSISTENT_CACHE_POOLS, ...self::RESET_CACHE_POOLS] as $cachePoolId) {
            /** @var \Psr\Cache\CacheItemPoolInterface $cachePool */
            $cachePool = $container->get($cachePoolId);
            $cachePool->save($cachePool->getItem(self::CACHE_ITEM_KEY)->set('value'));
        }
        /** @var \Symfony\Contracts\Service\ResetInterface $sut */
        $sut = $container->get('services_resetter');

        $sut->reset();

        foreach (self::PERSISTENT_CACHE_POOLS as $cachePoolId) {
            /** @var \Psr\Cache\CacheItemPoolInterface $cachePool */
            $cachePool = $container->get($cachePoolId);
            self::assertTrue(
                $cachePool->hasItem(self::CACHE_ITEM_KEY),
                \sprintf('Cache pool "%s" must not be reset between Lambda invocations.', $cachePoolId)
            );
        }
        // Pools not extending "cache.app" or "cache.system", and chain pools, are still reset
        foreach (self::RESET_CACHE_POOLS as $cachePoolId) {
            /** @var \Psr\Cache\CacheItemPoolInterface $cachePool */
            $cachePool = $container->get($cachePoolId);
            self::assertFalse(
                $cachePool->hasItem(self::CACHE_ITEM_KEY),
                \sprintf('Cache pool "%s" must be reset between Lambda invocations.', $cachePoolId)
            );
        }
    }

    public function testServicesResetterResetsSystemCachePoolsOutsideLambda(): void
    {
        $kernel = new FrameworkKernelStub(
            'test',
            true,
            [__DIR__ . '/../../../Fixture/config/framework_array_cache.php']
        );
        $kernel->boot();
        $container = $kernel->getContainer();
        foreach ([...self::PERSISTENT_CACHE_POOLS, ...self::RESET_CACHE_POOLS] as $cachePoolId) {
            /** @var \Psr\Cache\CacheItemPoolInterface $cachePool */
            $cachePool = $container->get($cachePoolId);
            $cachePool->save($cachePool->getItem(self::CACHE_ITEM_KEY)->set('value'));
        }
        /** @var \Symfony\Contracts\Service\ResetInterface $sut */
        $sut = $container->get('services_resetter');

        $sut->reset();

        foreach ([...self::PERSISTENT_CACHE_POOLS, ...self::RESET_CACHE_POOLS] as $cachePoolId) {
            /** @var \Psr\Cache\CacheItemPoolInterface $cachePool */
            $cachePool = $container->get($cachePoolId);
            self::assertFalse(
                $cachePool->hasItem(self::CACHE_ITEM_KEY),
                \sprintf('Cache pool "%s" must be reset outside Lambda.', $cachePoolId)
            );
        }
    }
}
