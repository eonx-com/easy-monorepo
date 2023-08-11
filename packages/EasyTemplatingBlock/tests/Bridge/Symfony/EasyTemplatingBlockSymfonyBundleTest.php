<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Tests\Bridge\Symfony;

use EonX\EasyTemplatingBlock\Interfaces\TemplatingEventRendererInterface;
use PHPUnit\Framework\Attributes\DataProvider;

final class EasyTemplatingBlockSymfonyBundleTest extends AbstractSymfonyTestCase
{
    /**
     * @see testRenderEvent
     */
    public static function providerTestRenderEvent(): iterable
    {
        yield 'No block provider for event' => [
            [
                'my-event' => '',
            ],
            null,
        ];

        yield 'One template block no context' => [
            [
                'my-event' => "<span>Hello EonX!</span>\n",
            ],
            null,
            [
                __DIR__ . '/Fixtures/config/one_template_block.yaml',
            ],
        ];

        yield 'One template block with context' => [
            [
                'my-event' => "<span>Hello Nathan!</span>\n",
            ],
            [
                'name' => 'Nathan',
            ],
            [
                __DIR__ . '/Fixtures/config/one_template_block.yaml',
            ],
        ];
    }

    /**
     * @param string[]|null $configs
     */
    #[DataProvider('providerTestRenderEvent')]
    public function testRenderEvent(array $events, ?array $context, ?array $configs = null): void
    {
        $kernel = $this->getKernel($configs);
        $templatingEventRenderer = $kernel->getContainer()
            ->get(TemplatingEventRendererInterface::class);

        foreach ($events as $event => $expectedRendered) {
            $rendered = $templatingEventRenderer->renderEvent($event, $context);

            self::assertEquals($expectedRendered, $rendered);
        }
    }

    public function testSanity(): void
    {
        $templatingEventRenderer = $this->getKernel()
            ->getContainer()
            ->get(TemplatingEventRendererInterface::class);

        self::assertInstanceOf(TemplatingEventRendererInterface::class, $templatingEventRenderer);
    }
}
