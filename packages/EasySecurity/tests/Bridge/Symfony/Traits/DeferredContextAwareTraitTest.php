<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Traits;

use EonX\EasySecurity\Bridge\Symfony\Helpers\DeferredContextResolver;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasySecurity\Tests\Bridge\Symfony\Stubs\DeferredContextAwareTraitStub;

final class DeferredContextAwareTraitTest extends AbstractSymfonyTestCase
{
    /**
     * Trait should use deferred context resolver to resolver context.
     *
     * @return void
     */
    public function testResolveContext(): void
    {
        $context = new Context();
        $container = $this->getKernel()->getContainer();

        $container->set('service-id', $context);

        $stub = new DeferredContextAwareTraitStub();
        $stub->setDeferredContextResolver(new DeferredContextResolver($container, 'service-id'));

        self::assertSame($context, $stub->getContext());
        self::assertSame($context, $stub->getContext());
    }
}
