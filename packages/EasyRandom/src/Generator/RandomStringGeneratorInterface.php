<?php
declare(strict_types=1);

namespace EonX\EasyRandom\Generator;

use EonX\EasyRandom\ValueObject\RandomString;

interface RandomStringGeneratorInterface
{
    public function generate(int $length): RandomString;
}
