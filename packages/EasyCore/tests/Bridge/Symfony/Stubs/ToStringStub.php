<?php
declare(strict_types=1);

namespace EonX\EasyCore\Tests\Bridge\Symfony\Stubs;

final class ToStringStub
{
    public function __toString()
    {
        return static::class;
    }
}
