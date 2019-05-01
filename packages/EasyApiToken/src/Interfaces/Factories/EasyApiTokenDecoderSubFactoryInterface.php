<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Interfaces\Factories;

use LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;

interface EasyApiTokenDecoderSubFactoryInterface
{
    /**
     * Build api token decoder for given config.
     *
     * @param null|mixed[] $config
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \LoyaltyCorp\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface;
}
