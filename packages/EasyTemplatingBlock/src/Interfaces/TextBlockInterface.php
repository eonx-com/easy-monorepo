<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Interfaces;

interface TextBlockInterface extends TemplatingBlockInterface
{
    public function getContents(): string;
}
