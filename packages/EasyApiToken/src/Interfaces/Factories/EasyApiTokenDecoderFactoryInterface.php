<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use Psr\Container\ContainerInterface;

interface EasyApiTokenDecoderFactoryInterface
{
    /**
     * Build given api token decoder.
     *
     * @param string $decoder
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
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
