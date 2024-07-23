<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Fixture\App\Provider;

use EonX\EasyRandom\Generator\RandomGeneratorInterface;

final class UuidProvider
{
    public function __construct(
        private readonly RandomGeneratorInterface $randomGenerator,
    ) {
    }

    public function provide(): string
    {
        return $this->randomGenerator->uuid();
    }
}
