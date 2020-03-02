<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Data;

use EonX\EasyAsync\Interfaces\TargetInterface;

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
     * Target constructor.
     *
     * @param mixed $id
     * @param string $type
     */
    public function __construct($id, string $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    /**
     * Get target id.
     *
     * @return mixed
     */
    public function getTargetId()
    {
        return $this->id;
    }

    /**
     * Get target type.
     *
     * @return string
     */
    public function getTargetType(): string
    {
        return $this->type;
    }
}
