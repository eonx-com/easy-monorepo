<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Common\ValueObject;

use EonX\EasySwoole\Common\Enum\SwooleTableColumnType;

final readonly class SwooleTableColumnDefinition
{
    public function __construct(
        public string $name,
        public SwooleTableColumnType $type,
        public ?int $size = null,
    ) {
    }
}
