<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Interfaces\Factories;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;

interface EasyApiTokenDecoderSubFactoryInterface
{
    /**
     * Build api token decoder for given config.
     *
     * @param null|mixed[] $config
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function build(?array $config = null): EasyApiTokenDecoderInterface;
}
