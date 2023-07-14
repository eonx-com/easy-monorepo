<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Bridge\Symfony\Twig;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingEventRendererInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TwigBlockExtension extends AbstractExtension
{
    public function __construct(
        private TemplatingEventRendererInterface $templatingEventRenderer,
    ) {
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('render_templating_event', [$this, 'render'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * @param null|mixed[] $context
     */
    public function render(string $event, ?array $context = null): string
    {
        return $this->templatingEventRenderer->renderEvent($event, $context);
    }
}
