<?php

declare(strict_types=1);

namespace EonX\EasySwoole\ValueObjects;

use EonX\EasySwoole\Enums\SwooleTableColumnType;

final class SwooleTableColumnDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly SwooleTableColumnType $type,
        public readonly ?int $size = null,
    ) {
    }
}
