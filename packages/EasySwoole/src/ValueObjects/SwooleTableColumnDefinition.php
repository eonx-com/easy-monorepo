<?php

declare(strict_types=1);

namespace EonX\EasySwoole\ValueObjects;

use EonX\EasySwoole\Enums\SwooleTableColumnType;

final class SwooleTableColumnDefinition
{
    public function __construct(
        readonly public string $name,
        readonly public SwooleTableColumnType $type,
        readonly public ?int $size = null,
    ) {
    }
}
