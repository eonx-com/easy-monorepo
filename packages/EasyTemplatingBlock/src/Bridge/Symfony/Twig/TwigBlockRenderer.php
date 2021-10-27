<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge\Symfony\Twig;

use EonX\EasyTemplatingBlock\Exceptions\UnableToRenderBlockException;
use EonX\EasyTemplatingBlock\Interfaces\TemplateBlockInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface;
use EonX\EasyTemplatingBlock\Renderers\AbstractSimpleTemplatingBlockRenderer;
use Twig\Environment;

final class TwigBlockRenderer extends AbstractSimpleTemplatingBlockRenderer
{
    /**
     * @var \Twig\Environment
     */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @param \EonX\EasyTemplatingBlock\Interfaces\TemplateBlockInterface $block
     */
    public function renderBlock(TemplatingBlockInterface $block): string
    {
        try {
            return $this->twig->render($block->getTemplateName(), $block->getTemplateContext() ?? []);
        } catch (\Throwable $throwable) {
            throw new UnableToRenderBlockException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }
    }

    /**
     * @return string[]
     */
    protected function getSupportedBlockClasses(): array
    {
        return [TemplateBlockInterface::class];
    }
}
