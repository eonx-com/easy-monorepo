<?php
declare(strict_types=1);

namespace LoyaltyCorp\EasyApiToken\Tests\Bridge\Symfony\Stubs;


use LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface;

final class ServiceStub
{
    /**
     * @var \LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    private $decoderFactory;

    /**
     * ServiceStub constructor.
     *
     * @param \LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface $decoderFactory
     */
    public function __construct(EasyApiTokenDecoderFactoryInterface $decoderFactory)
    {
        $this->decoderFactory = $decoderFactory;
    }

    /**
     * Get decoder factory.
     *
     * @return \LoyaltyCorp\EasyApiToken\Interfaces\Factories\EasyApiTokenDecoderFactoryInterface
     */
    public function getDecoderFactory(): EasyApiTokenDecoderFactoryInterface
    {
        return $this->decoderFactory;
    }
}
