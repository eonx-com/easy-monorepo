<?php
declare(strict_types=1);

namespace EonX\EasyCfhighlander\Interfaces;

use Symfony\Component\Console\Input\InputInterface;

interface ParameterResolverInterface
{
    /**
     * Add modifier callable for given param name.
     *
     * @param string $param
     * @param callable $modifier
     *
     * @return \EonX\EasyCfhighlander\Interfaces\ParameterResolverInterface
     */
    public function addModifier(string $param, callable $modifier): self;

    /**
     * Add resolver callable for given param name.
     *
     * @param string $param
     * @param callable $resolver
     *
     * @return \EonX\EasyCfhighlander\Interfaces\ParameterResolverInterface
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
     * @return \EonX\EasyCfhighlander\Interfaces\ParameterResolverInterface
     */
    public function setCachePathname(string $pathname): self;
}
