<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\ValueObject;

interface TextBlockInterface extends TemplatingBlockInterface
{
    public function getContents(): string;
}
