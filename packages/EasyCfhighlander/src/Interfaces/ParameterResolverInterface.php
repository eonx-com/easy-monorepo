<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Interfaces;

use Symfony\Component\Console\Input\InputInterface;

interface ParameterResolverInterface
{
    /**
     * Add resolver callable.
     *
     * @param callable $resolver
     *
     * @return \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface
     */
    public function addResolver(callable $resolver): self;

    /**
     * Resolve parameters.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return mixed[]
     */
    public function resolve(InputInterface $input): array;
}
