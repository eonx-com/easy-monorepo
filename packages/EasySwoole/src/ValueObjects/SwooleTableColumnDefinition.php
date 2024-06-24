<?php
declare(strict_types=1);

namespace EonX\EasySwoole\ValueObjects;

use EonX\EasySwoole\Enums\SwooleTableColumnType;

final readonly class SwooleTableColumnDefinition
{
    public function __construct(
        public string $name,
        public SwooleTableColumnType $type,
        public ?int $size = null,
    ) {
    }
}
