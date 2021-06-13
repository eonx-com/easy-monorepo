<?php

declare(strict_types=1);

namespace EonX\EasyBatch\Interfaces;

interface BatchItemWithClassInterface
{
    public function getClass(): string;
}
