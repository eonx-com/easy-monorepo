<?php
declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use Psr\Container\ContainerInterface;

interface DecisionFactoryInterface
{
    /**
     * Create decision for given config.
     *
     * @param \EonX\EasyDecision\Interfaces\DecisionConfigInterface $config
     *
     * @return \EonX\EasyDecision\Interfaces\DecisionInterface
     */
    public function create(DecisionConfigInterface $config): DecisionInterface;

    /**
     * Set container.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container): void;
}
