<?php
declare(strict_types=1);

namespace EonX\EasySecurity\Bridge\Symfony\Factories;

use EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface;
use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface as BaseFactoryInterface;

final class EasyApiTokenDecoderFactory
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    private $factory;

    /**
     * EasyApiTokenDecoderFactory constructor.
     *
     * @param \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface $factory
     */
    public function __construct(BaseFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Create easy-api-token decoder.
     *
     * @param string $decoder
     *
     * @return \EonX\EasyApiToken\Interfaces\EasyApiTokenDecoderInterface
     *
     * @throws \EonX\EasyApiToken\Exceptions\InvalidConfigurationException
     */
    public function __invoke(string $decoder): EasyApiTokenDecoderInterface
    {
        return $this->factory->build($decoder);
    }
}
