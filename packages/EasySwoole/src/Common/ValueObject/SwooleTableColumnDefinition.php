<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\ValueObject;

use EonX\EasySwoole\Common\Enum\SwooleTableColumnType;

final class SwooleTableColumnDefinition
{
    public function __construct(
        public readonly string $name,
        public readonly SwooleTableColumnType $type,
        public readonly ?int $size = null,
    ) {
    }
}
