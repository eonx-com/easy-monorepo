<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Resolvers;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface;
use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextDataResolverInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolverInterface;
use Symfony\Component\HttpFoundation\Request;
use Traversable;

final class ContextResolver implements ContextResolverInterface
{
    /**
     * @var \EonX\EasySecurity\Interfaces\Resolvers\ContextDataResolverInterface[]
     */
    private $contextDataResolvers;

    /**
     * @var \EonX\EasySecurity\Interfaces\ContextFactoryInterface
     */
    private $contextFactory;

    /**
     * @var \EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface
     */
    private $psr7Factory;

    /**
     * @var \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     */
    private $tokenDecoder;

    /**
     * ContextResolver constructor.
     *
     * @param \EonX\EasySecurity\Interfaces\ContextFactoryInterface $contextFactory
     * @param \EonX\EasyPsr7Factory\Interfaces\EasyPsr7FactoryInterface $psr7Factory
     * @param \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface $tokenDecoder
     * @param mixed[]|iterable<mixed> $contextDataResolvers
     */
    public function __construct(
        ContextFactoryInterface $contextFactory,
        EasyPsr7FactoryInterface $psr7Factory,
        EasyApiTokenDecoderInterface $tokenDecoder,
        $contextDataResolvers
    ) {
        $this->contextFactory = $contextFactory;
        $this->psr7Factory = $psr7Factory;
        $this->tokenDecoder = $tokenDecoder;

        $this->setDataResolvers($contextDataResolvers);
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
        $data = new ContextResolvingData(
            $request,
            $this->tokenDecoder->decode($this->psr7Factory->createRequest($request))
        );

        foreach ($this->contextDataResolvers as $resolver) {
            $data = $resolver->resolve($data);
        }

        return $this->contextFactory->create($data);
    }

    /**
     * Filter and sort by priority context data resolvers.
     *
     * @param mixed[]|iterable<mixed> $resolvers
     *
     * @return void
     */
    private function setDataResolvers($resolvers): void
    {
        $resolvers = $resolvers instanceof Traversable ? \iterator_to_array($resolvers) : (array)$resolvers;

        $filter = static function ($resolver): bool {
            return $resolver instanceof ContextDataResolverInterface;
        };
        $sort = static function (ContextDataResolverInterface $first, ContextDataResolverInterface $second): int {
            return $first->getPriority() <=> $second->getPriority();
        };

        $resolvers = \array_filter($resolvers, $filter);
        \usort($rules, $sort);

        $this->contextDataResolvers = $resolvers;
    }
}
