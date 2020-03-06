<?php
declare(strict_types=1);

namespace EonX\EasyAsync\Interfaces;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
