<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Renderers;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockRendererInterface;
use EonX\EasyUtils\Common\Helper\HasPriorityTrait;

abstract class AbstractSimpleTemplatingBlockRenderer implements TemplatingBlockRendererInterface
{
    use HasPriorityTrait;

    public function supports(TemplatingBlockInterface $block): bool
    {
        foreach ($this->getSupportedBlockClasses() as $blockClass) {
            if ($block instanceof $blockClass) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    abstract protected function getSupportedBlockClasses(): array;
}
