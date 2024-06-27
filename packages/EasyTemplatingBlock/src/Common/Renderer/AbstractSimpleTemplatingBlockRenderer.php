<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface;
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
