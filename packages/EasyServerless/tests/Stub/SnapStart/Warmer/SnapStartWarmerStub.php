<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\SnapStart\Warmer;

use ArrayObject;
use EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface;
use Throwable;

final readonly class SnapStartWarmerStub implements SnapStartWarmerInterface
{
    /**
     * @param \ArrayObject<int, string> $calls
     */
    public function __construct(
        private ArrayObject $calls = new ArrayObject(),
        private string $name = 'warmer',
        private ?Throwable $throwable = null,
    ) {
    }

    public function warm(): void
    {
        $this->calls->append($this->name);

        if ($this->throwable !== null) {
            throw $this->throwable;
        }
    }
}
