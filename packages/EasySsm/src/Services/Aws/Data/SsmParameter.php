<?php

declare(strict_types=1);

namespace EonX\EasySsm\Services\Aws\Data;

final class SsmParameter
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $type;

    /**
     * @var string
     */
    private $value;

    public function __construct(string $name, string $type, string $value)
    {
        $this->name = $name;
        $this->type = $type;
        $this->value = $value;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return [
            'Name' => $this->getName(),
            'Type' => $this->getType(),
            'Value' => $this->getValue(),
        ];
    }
}
