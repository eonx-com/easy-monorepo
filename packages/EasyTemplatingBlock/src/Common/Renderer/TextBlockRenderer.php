<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\ValueObject\AbstractTemplatingBlock;
use EonX\EasyTemplatingBlock\Common\ValueObject\TextBlock;

final class TextBlockRenderer extends AbstractSimpleTemplatingBlockRenderer
{
    /**
     * @param \EonX\EasyTemplatingBlock\Common\ValueObject\TextBlock $block
     */
    public function renderBlock(AbstractTemplatingBlock $block): string
    {
        return $block->getContents();
    }

    /**
     * @return string[]
     */
    protected function getSupportedBlockClasses(): array
    {
        return [TextBlock::class];
    }
}
