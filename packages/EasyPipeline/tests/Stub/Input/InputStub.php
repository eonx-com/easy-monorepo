<?php
declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Stub\Input;

final class InputStub
{
    public function __construct(
        private string $name,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
