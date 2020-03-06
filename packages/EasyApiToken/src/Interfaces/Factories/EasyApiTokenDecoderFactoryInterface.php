<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use Psr\Container\ContainerInterface;

interface EasyApiTokenDecoderFactoryInterface
{
    public function build(string $decoder): EasyApiTokenDecoderInterface;

    public function setContainer(ContainerInterface $container): void;
}
