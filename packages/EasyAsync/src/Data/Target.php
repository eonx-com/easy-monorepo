<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Interfaces\TargetInterface;

/**
 * @deprecated since 3.0.0, will be removed in 3.1. Use Batch features instead.
 */
final class Target implements TargetInterface
{
    /**
     * @var mixed
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @param mixed $id
     */
    public function __construct($id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->id;
    }

    public function getTargetType(): string
    {
        return $this->type;
    }
}
