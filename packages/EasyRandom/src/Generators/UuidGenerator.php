<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generators;

use EonX\EasyRandom\Interfaces\UuidGeneratorInterface;
use Symfony\Component\Uid\Factory\UuidFactory;

final class UuidGenerator implements UuidGeneratorInterface
{
    public function __construct(
        private UuidFactory $uuidFactory,
    ) {
    }

    public function generate(): string
    {
        return (string)$this->uuidFactory->create();
    }
}
