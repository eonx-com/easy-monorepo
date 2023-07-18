<?php

declare(strict_types=1);

namespace EonX\EasyRandom\Interfaces;

interface RandomStringGeneratorInterface
{
    public function generate(int $length): RandomStringInterface;
}
