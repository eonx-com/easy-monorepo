<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\ValueObject;

interface TemplateBlockInterface extends TemplatingBlockInterface
{
    public function getTemplateContext(): ?array;

    public function getTemplateName(): string;
}
