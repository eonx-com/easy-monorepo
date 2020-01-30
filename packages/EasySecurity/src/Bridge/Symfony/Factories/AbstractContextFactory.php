<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Factories;

use EonX\EasySecurity\Interfaces\ContextFactoryInterface;
use EonX\EasySecurity\Interfaces\ContextInterface;
use EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractContextFactory implements ContextFactoryInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * Create context.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    public function create(ContextResolvingDataInterface $data): ContextInterface
    {
        $context = $this->doCreate($data);

        $this->container->set($this->getServiceId(), $context);

        return $context;
    }

    /**
     * Set container.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     *
     * @return void
     *
     * @required
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Delegate context creation to children.
     *
     * @param \EonX\EasySecurity\Interfaces\Resolvers\ContextResolvingDataInterface $data
     *
     * @return \EonX\EasySecurity\Interfaces\ContextInterface
     */
    abstract protected function doCreate(ContextResolvingDataInterface $data): ContextInterface;

    /**
     * Get service id for created context.
     *
     * @return string
     */
    abstract protected function getServiceId(): string;
}
