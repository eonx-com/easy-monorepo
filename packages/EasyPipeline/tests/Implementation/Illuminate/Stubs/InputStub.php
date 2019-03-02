<?php
declare(strict_types=1);

namespace StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs;

final class InputStub
{
    /**
     * @var string
     */
    private $name;

    /**
     * InputStub constructor.
     *
     * @param string $initialName
     */
    public function __construct(string $initialName)
    {
        $this->name = $initialName;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return \StepTheFkUp\EasyPipeline\Tests\Implementation\Illuminate\Stubs\InputStub
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
