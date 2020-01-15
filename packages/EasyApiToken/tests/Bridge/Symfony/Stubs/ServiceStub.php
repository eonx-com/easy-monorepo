<?php
declare(strict_types=1);

namespace EonX\EasyApiToken\Tests\Bridge\Symfony\Stubs;

use EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;

final class ServiceStub
{
    /**
     * @var \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    private $decoderFactory;

    /**
     * ServiceStub constructor.
     *
     * @param \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface $decoderFactory
     */
    public function __construct(EasyApiTokenDecoderFactoryInterface $decoderFactory)
    {
        $this->decoderFactory = $decoderFactory;
    }

    /**
     * Get decoder factory.
     *
     * @return \EonX\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    public function getDecoderFactory(): EasyApiTokenDecoderFactoryInterface
    {
        return $this->decoderFactory;
    }
}
