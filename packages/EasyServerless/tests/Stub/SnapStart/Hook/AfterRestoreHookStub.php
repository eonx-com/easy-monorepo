<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Stub\SnapStart\Hook;

use ArrayObject;
use EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface;
use Throwable;

final readonly class AfterRestoreHookStub implements AfterRestoreHookInterface
{
    /**
     * @param \ArrayObject<int, string> $calls
     */
    public function __construct(
        private ArrayObject $calls = new ArrayObject(),
        private string $name = 'hook',
        private ?Throwable $throwable = null,
    ) {
    }

    public function afterRestore(): void
    {
        $this->calls->append($this->name);

        if ($this->throwable !== null) {
            throw $this->throwable;
        }
    }
}
