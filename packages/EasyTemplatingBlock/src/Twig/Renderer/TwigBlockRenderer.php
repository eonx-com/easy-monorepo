<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Twig\Renderer;

use EonX\EasyTemplatingBlock\Common\Renderer\AbstractSimpleTemplatingBlockRenderer;
use EonX\EasyTemplatingBlock\Common\ValueObject\AbstractTemplatingBlock;
use EonX\EasyTemplatingBlock\Common\ValueObject\TemplateBlock;
use EonX\EasyTemplatingBlock\Twig\Exception\UnableToRenderBlockException;
use Throwable;
use Twig\Environment;

final class TwigBlockRenderer extends AbstractSimpleTemplatingBlockRenderer
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    /**
     * @param \EonX\EasyTemplatingBlock\Common\ValueObject\TemplateBlock $block
     */
    public function renderBlock(AbstractTemplatingBlock $block): string
    {
        try {
            return $this->twig->render($block->getTemplateName(), $block->getTemplateContext() ?? []);
        } catch (Throwable $throwable) {
            throw new UnableToRenderBlockException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @return string[]
     */
    protected function getSupportedBlockClasses(): array
    {
        return [TemplateBlock::class];
    }
}
