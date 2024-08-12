<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomStringConfig;

interface RandomStringGeneratorInterface
{
    public function generate(RandomStringConfig $randomStringConfig): string;
}
