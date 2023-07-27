<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Renderers;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface;
use EonX\EasyTemplatingBlock\Interfaces\TextBlockInterface;

final class TextBlockRenderer extends AbstractSimpleTemplatingBlockRenderer
{
    /**
     * @param \EonX\EasyTemplatingBlock\Interfaces\TextBlockInterface $block
     */
    public function renderBlock(TemplatingBlockInterface $block): string
    {
        return $block->getContents();
    }

    /**
     * @return string[]
     */
    protected function getSupportedBlockClasses(): array
    {
        return [TextBlockInterface::class];
    }
}
