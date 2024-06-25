<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Tests\Unit\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\Provider\ArrayTemplatingBlockProvider;
use EonX\EasyTemplatingBlock\Common\Renderer\TemplatingEventRenderer;
use EonX\EasyTemplatingBlock\Common\Renderer\TextBlockRenderer;
use EonX\EasyTemplatingBlock\Common\ValueObject\TextBlock;
use EonX\EasyTemplatingBlock\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TemplatingEventRendererTest extends AbstractUnitTestCase
{
    /**
     * @see testRenderEvent
     */
    public static function providerTestRenderEvent(): iterable
    {
        yield 'No block for event' => [
            [
                'my-event' => '',
            ],
            null,
            [
                new ArrayTemplatingBlockProvider([]),
            ],
        ];

        yield 'Simple text block' => [
            [
                'my-event' => 'my event text',
            ],
            null,
            [
                new ArrayTemplatingBlockProvider([
                    'my-event' => [
                        TextBlock::create('my-text-block', 'my event text'),
                    ],
                ]),
            ],
        ];

        yield 'Multiple simple blocks' => [
            [
                'my-event' => "text\ntext",
            ],
            null,
            [
                new ArrayTemplatingBlockProvider([
                    'my-event' => [
                        (new TextBlock('my-text-block-1'))->setContents('text'),
                        (new TextBlock('my-text-block-1'))->setContents('text'),
                    ],
                ]),
            ],
        ];
    }

    /**
     * @param \EonX\EasyTemplatingBlock\Common\Provider\TemplatingBlockProviderInterface[] $providers
     * @param \EonX\EasyTemplatingBlock\Common\Renderer\TemplatingBlockRendererInterface[]|null $renderers
     */
    #[DataProvider('providerTestRenderEvent')]
    public function testRenderEvent(
        array $events,
        ?array $context,
        array $providers,
        ?array $renderers = null,
        ?bool $isDebug = null,
    ): void {
        $renderers ??= [new TextBlockRenderer()];
        $eventRenderer = new TemplatingEventRenderer($providers, $renderers, $isDebug ?? false);

        foreach ($events as $event => $expectedRendered) {
            $rendered = $eventRenderer->renderEvent($event, $context);

            self::assertEquals($expectedRendered, $rendered);
        }
    }
}
