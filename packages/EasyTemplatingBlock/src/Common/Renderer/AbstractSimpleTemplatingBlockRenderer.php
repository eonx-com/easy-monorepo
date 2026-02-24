<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\ValueObject\AbstractTemplatingBlock;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractSimpleTemplatingBlockRenderer implements TemplatingBlockRendererInterface
{
    use HasPriorityTrait;

    public function supports(AbstractTemplatingBlock $block): bool
    {
        return \array_any($this->getSupportedBlockClasses(), static fn ($blockClass) => $block instanceof $blockClass);
    }

    /**
     * @return string[]
     */
    abstract protected function getSupportedBlockClasses(): array;
}
