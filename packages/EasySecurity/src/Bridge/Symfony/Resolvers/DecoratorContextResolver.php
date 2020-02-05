<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Resolvers;

use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

final class DecoratorContextResolver implements ContextResolverInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $contextServiceId;

    /**
     * @var \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface
     */
    private $decorated;

    /**
     * DecoratorContextResolver constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface $decorated
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param string $contextServiceId
     */
    public function __construct(
        ContextResolverInterface $decorated,
        ContainerInterface $container,
        string $contextServiceId
    ) {
        $this->decorated = $decorated;
        $this->container = $container;
        $this->contextServiceId = $contextServiceId;
    }

    /**
     * Resolve context for given request.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function resolve(Request $request): ContextInterface
    {
        $context = $this->decorated->resolve($request);

        \dump('DECORATED RESOLVER CONTAINER HASH', \spl_object_hash($this->container));
        \dump('DECORATED RESOLVER, set, CONTEXT HASH', \spl_object_hash($context));
        \dump('DECORATED RESOLVER, set', $context);

        $this->container->set($this->contextServiceId, $context);

        \dump('DECORATED RESOLVER, get', $this->container->get($this->contextServiceId));
        \dump('DECORATED RESOLVER, get, CONTEXT HASH', \spl_object_hash($this->container->get($this->contextServiceId)));

        return $context;
    }
}
