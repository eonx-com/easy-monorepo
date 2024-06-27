<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface TemplatingBlockRendererInterface extends HasPriorityInterface
{
    public function renderBlock(TemplatingBlockInterface $block): string;

    public function supports(TemplatingBlockInterface $block): bool;
}
