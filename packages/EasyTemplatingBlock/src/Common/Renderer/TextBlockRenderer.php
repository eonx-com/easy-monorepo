<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface;
use EonX\EasyTemplatingBlock\Common\ValueObject\TextBlockInterface;

final class TextBlockRenderer extends AbstractSimpleTemplatingBlockRenderer
{
    /**
     * @param \EonX\EasyTemplatingBlock\Common\ValueObject\TextBlockInterface $block
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
