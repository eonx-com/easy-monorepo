<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Tests\Bridge\Symfony\Resolvers;

use EonX\EasySecurity\Bridge\Symfony\Resolvers\DecoratorContextResolver;
use EonX\EasySecurity\Context;
use EonX\EasySecurity\Tests\Bridge\Symfony\AbstractSymfonyTestCase;
use EonX\EasySecurity\Tests\Bridge\Symfony\Stubs\ContextResolverStub;
use Symfony\Component\HttpFoundation\Request;

final class DecoratorContextResolverTest extends AbstractSymfonyTestCase
{
    /**
     * DecoratorContextResolver should set instance of resolved context in container.
     *
     * @return void
     */
    public function testContextSetOnContainer(): void
    {
        $context = new Context();
        $container = $this->getKernel()->getContainer();

        $resolver = new DecoratorContextResolver(new ContextResolverStub($context), $container, 'service-id');

        self::assertSame($context, $resolver->resolve(new Request()));
        self::assertSame($context, $container->get('service-id'));
    }
}
