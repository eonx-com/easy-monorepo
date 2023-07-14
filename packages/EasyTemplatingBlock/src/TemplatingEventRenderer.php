<?php

declare(strict_types=1);

namespace EonX\EasyTemplatingBlock;

use EonX\EasyTemplatingBlock\Exceptions\NoRendererFoundForBlockException;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockProviderInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockRendererInterface;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingEventRendererInterface;
use EonX\EasyUtils\Helpers\CollectorHelper;

final class TemplatingEventRenderer implements TemplatingEventRendererInterface
{
    private bool $isDebug;

    /**
     * @var \EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockProviderInterface[]
     */
    private array $providers;

    /**
     * @var \EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockRendererInterface[]
     */
    private array $renderers;

    /**
     * @param iterable<\EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockProviderInterface> $providers
     * @param iterable<\EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockRendererInterface> $renderers
     */
    public function __construct(iterable $providers, iterable $renderers, ?bool $isDebug = null)
    {
        $this->providers = CollectorHelper::filterByClassAsArray($providers, TemplatingBlockProviderInterface::class);
        $this->renderers = CollectorHelper::orderLowerPriorityFirstAsArray(
            CollectorHelper::filterByClass($renderers, TemplatingBlockRendererInterface::class)
        );
        $this->isDebug = $isDebug ?? true;
    }

    /**
     * @param null|mixed[] $context
     */
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
            \get_class($block),
            $block->getName()
        ));
    }

    /**
     * @param \EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface[] $blocks
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
     * @param null|mixed[] $context
     *
     * @return \EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface[]
     */
    private function resolveBlocksForEvent(string $event, ?array $context = null): array
    {
        $blocks = [];

        foreach ($this->providers as $provider) {
            /** @var iterable<\EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface> $providedBlocks */
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
