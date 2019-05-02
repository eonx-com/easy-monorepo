<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Factories;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use Psr\Container\ContainerInterface;

interface EasyApiTokenDecoderFactoryInterface
{
    /**
     * Build given api token decoder.
     *
     * @param string $decoder
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(string $decoder): EasyApiTokenDecoderInterface;

    /**
     * Set PSR container.
     *
     * @param \Psr\Container\ContainerInterface $container
     *
     * @return void
     */
    public function setContainer(ContainerInterface $container): void;
}
