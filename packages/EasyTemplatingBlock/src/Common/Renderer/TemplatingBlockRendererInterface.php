<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\ValueObject\AbstractTemplatingBlock;
use EonX\EasyUtils\Common\Helper\HasPriorityInterface;

interface TemplatingBlockRendererInterface extends HasPriorityInterface
{
    public function renderBlock(AbstractTemplatingBlock $block): string;

    public function supports(AbstractTemplatingBlock $block): bool;
}
