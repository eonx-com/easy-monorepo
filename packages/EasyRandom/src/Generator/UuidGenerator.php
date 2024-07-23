<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use Symfony\Component\Uid\Factory\UuidFactory;

final readonly class UuidGenerator implements UuidGeneratorInterface
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
