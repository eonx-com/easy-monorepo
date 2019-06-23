<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyCfhighlander\Interfaces;

use Symfony\Component\Console\Input\InputInterface;

interface ParameterResolverInterface
{
    /**
     * Add resolver callable for given param name.
     *
     * @param string $param
     * @param callable $resolver
     *
     * @return \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface
     */
    public function addResolver(string $param, callable $resolver): self;

    /**
     * Resolve parameters.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     *
     * @return mixed[]
     */
    public function resolve(InputInterface $input): array;

    /**
     * Set cache pathname to use to store previous parameters.
     *
     * @param string $pathname
     *
     * @return \LoyaltyCorp\EasyCfhighlander\Interfaces\ParameterResolverInterface
     */
    public function setCachePathname(string $pathname): self;
}
