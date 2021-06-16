<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Objects;

abstract class AbstractObjectDecorator
{
    /**
     * @var object
     */
    private $object;

    public function __construct(object $object)
    {
        $this->object = $object;
    }

    public function getObject(): object
    {
        return $this->object;
    }
}
