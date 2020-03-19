<?php

declare(strict_types=1);

namespace EonX\EasyDecision\Interfaces;

use Psr\Container\ContainerInterface;

interface DecisionFactoryInterface
{
    public function create(DecisionConfigInterface $config): DecisionInterface;

    public function setContainer(ContainerInterface $container): void;
}
