<?php
declare(strict_types=1);

namespace EonX\EasyActivity\Bridge\Symfony\Uid;

use EonX\EasyActivity\Interfaces\IdFactoryInterface;
use Symfony\Component\Uid\Factory\UuidFactory as SymfonyUuidFactory;

final class UuidFactory implements IdFactoryInterface
{
    public function __construct(
        private SymfonyUuidFactory $uuidFactory,
    ) {
        // The body is not required
    }

    public function create(): string
    {
        return (string)$this->uuidFactory->create();
    }
}
