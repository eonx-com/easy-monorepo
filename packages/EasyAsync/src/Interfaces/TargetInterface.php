<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface TargetInterface
{
    /**
     * @return mixed
     */
    public function getTargetId();

    public function getTargetType(): string;
}
