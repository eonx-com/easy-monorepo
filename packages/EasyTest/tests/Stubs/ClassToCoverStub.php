<?php
declare(strict_types=1);

namespace EonX\EasyTest\Tests\Stubs;

final class ClassToCoverStub
{
    /**
     * @var int
     */
    private $prop;

    public function setProp(int $prop): void
    {
        $this->prop = $prop;
    }

    public function getProp(): int
    {
        return $this->prop;
    }
}
