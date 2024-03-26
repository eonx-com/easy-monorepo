<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Tests\Bridge\Symfony\Fixtures\App\Provider;

use EonX\EasyRandom\Interfaces\RandomGeneratorInterface;

final class UuidProvider
{
    public function __construct(private readonly RandomGeneratorInterface $randomGenerator)
    {
    }

    public function provide(): string
    {
        return $this->randomGenerator->uuid();
    }
}
