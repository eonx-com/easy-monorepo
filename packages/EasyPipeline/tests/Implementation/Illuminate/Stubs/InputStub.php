<?php

declare(strict_types=1);

namespace EonX\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

final class InputStub
{
    /**
     * @var string
     */
    private $name;

    public function __construct(string $initialName)
    {
        $this->name = $initialName;
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
