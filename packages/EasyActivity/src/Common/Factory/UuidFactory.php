<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Common\Factory;

use Symfony\Component\Uid\Factory\UuidFactory as SymfonyUuidFactory;

final readonly class UuidFactory implements IdFactoryInterface
{
    public function __construct(
        private SymfonyUuidFactory $uuidFactory,
    ) {
    }

    public function create(): string
    {
        return (string)$this->uuidFactory->create();
    }
}
