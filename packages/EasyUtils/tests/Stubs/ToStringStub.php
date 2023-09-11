<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Stubs;

use Stringable;

final class ToStringStub implements Stringable
{
    public function __toString(): string
    {
        return self::class;
    }
}
