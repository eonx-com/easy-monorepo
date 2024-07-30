<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\Exception\NoRendererFoundForBlockException;
use EonX\EasyTemplatingBlock\Common\Provider\TemplatingBlockProviderInterface;
use EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface;
use EonX\EasyUtils\Common\Helper\CollectorHelper;

final class TemplatingEventRenderer implements TemplatingEventRendererInterface
{
    private readonly bool $isDebug;

    /**
     * @var \EonX\EasyTemplatingBlock\Common\Provider\TemplatingBlockProviderInterface[]
     */
    private readonly array $providers;

    /**
     * @var \EonX\EasyTemplatingBlock\Common\Renderer\TemplatingBlockRendererInterface[]
     */
    private readonly array $renderers;

    /**
     * @param iterable<\EonX\EasyTemplatingBlock\Common\Provider\TemplatingBlockProviderInterface> $providers
     * @param iterable<\EonX\EasyTemplatingBlock\Common\Renderer\TemplatingBlockRendererInterface> $renderers
     */
    public function __construct(iterable $providers, iterable $renderers, ?bool $isDebug = null)
    {
        $this->providers = CollectorHelper::filterByClassAsArray($providers, TemplatingBlockProviderInterface::class);
        $this->renderers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($renderers, TemplatingBlockRendererInterface::class)
        );
        $this->isDebug = $isDebug ?? true;
    }

    public function renderEvent(string $event, ?array $context = null): string
    {
        return $this->renderBlocks($event, $this->resolveBlocksForEvent($event, $context));
    }

    private function renderBlock(TemplatingBlockInterface $block): string
    {
        foreach ($this->renderers as $renderer) {
            if ($renderer->supports($block)) {
                return $renderer->renderBlock($block);
            }
        }

        throw new NoRendererFoundForBlockException(\sprintf(
            'No renderer found for block %s with name "%s"',
            $block::class,
            $block->getName()
        ));
    }

    /**
     * @param \EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface[] $blocks
     */
    private function renderBlocks(string $event, array $blocks): string
    {
        $output = [];

        foreach ($blocks as $block) {
            if ($this->isDebug) {
                $output[] = \sprintf(
                    '<!-- BEGIN BLOCK | event: "%s", block: "%s", priority: %d -->',
                    $event,
                    $block->getName(),
                    $block->getPriority()
                );
            }

            $output[] = $this->renderBlock($block);

            if ($this->isDebug) {
                $output[] = \sprintf(
                    '<!-- END BLOCK | event: "%s", block: "%s" -->',
                    $event,
                    $block->getName()
                );
            }
        }

        return \implode("\n", $output);
    }

    /**
     * @return \EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface[]
     */
    private function resolveBlocksForEvent(string $event, ?array $context = null): array
    {
        $blocks = [];

        foreach ($this->providers as $provider) {
            /** @var iterable<\EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface> $providedBlocks */
            $providedBlocks = CollectorHelper::filterByClass(
                $provider->getBlocksForEvent($event, $context),
                TemplatingBlockInterface::class
            );

            foreach ($providedBlocks as $block) {
                if (\is_array($block->getContext()) || \is_array($context)) {
                    $block->setContext(\array_merge($block->getContext() ?? [], $context ?? []));
                }

                $blocks[] = $block;
            }
        }

        return CollectorHelper::orderLowerPriorityFirstAsArray($blocks);
    }
}
