<?php

declare(strict_types=1);

namespace EonX\EasyLogging\Tests\Stubs;

final class InvokableStub
{
    /**
     * @param mixed[] $records
     *
     * @return mixed[]
     */
    public function __invoke(array $records): array
    {
        return $records;
    }
}
