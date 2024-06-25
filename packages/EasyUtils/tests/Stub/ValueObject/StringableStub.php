<?php
declare(strict_types=1);

namespace EonX\EasyUtils\Tests\Stub\ValueObject;

use Stringable;

final class StringableStub implements Stringable
{
    public function __toString(): string
    {
        return self::class;
    }
}
