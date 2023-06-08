<?php

declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Bridge\Symfony\Fixtures;

use EonX\EasyUtils\Bridge\Symfony\Validator\Constraints\NumberInteger;
use Symfony\Component\Validator\Constraints\DateTime;

final class NumberIntegerDummy
{
    #[NumberInteger(message: 'myMessage')]
    private int $a;

    #[DateTime(groups: ['my_group'], payload: 'some attached data')]
    private int $b;

    public function getA(): int
    {
        return $this->a;
    }

    public function getB(): int
    {
        return $this->b;
    }

    public function setA(int $a): void
    {
        $this->a = $a;
    }

    public function setB(int $b): void
    {
        $this->b = $b;
    }
}
