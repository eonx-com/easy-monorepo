<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface TargetInterface
{
    /**
     * Get target id.
     *
     * @return mixed
     */
    public function getTargetId();

    /**
     * Get target type.
     *
     * @return string
     */
    public function getTargetType(): string;
}
