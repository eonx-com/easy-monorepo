<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Tests\Renderers;

use EonX\EasyTemplatingBlock\Blocks\TemplateBlock;
use EonX\EasyTemplatingBlock\Blocks\TextBlock;
use EonX\EasyTemplatingBlock\Interfaces\TemplatingBlockInterface;
use EonX\EasyTemplatingBlock\Renderers\TextBlockRenderer;
use EonX\EasyTemplatingBlock\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TextBlockRendererTest extends AbstractTestCase
{
    /**
     * @see testRenderBlock
     */
    public static function providerTestRenderBlock(): iterable
    {
        yield 'Simple text' => [
            (new TextBlock('my-text-block'))->setContents('my simple contents'),
            'my simple contents',
        ];
    }

    /**
     * @see testSupports
     */
    public static function providerTestSupports(): iterable
    {
        yield 'Supported' => [
            new TextBlock('my-text-block'),
            true,
        ];

        yield 'Not Supported' => [
            new TemplateBlock('my-template-block'),
            false,
        ];
    }

    #[DataProvider('providerTestRenderBlock')]
    public function testRenderBlock(TextBlock $block, string $expected): void
    {
        $renderer = new TextBlockRenderer();

        self::assertEquals($expected, $renderer->renderBlock($block));
    }

    #[DataProvider('providerTestSupports')]
    public function testSupports(TemplatingBlockInterface $block, bool $expected): void
    {
        $renderer = new TextBlockRenderer();

        self::assertEquals($expected, $renderer->supports($block));
    }
}
