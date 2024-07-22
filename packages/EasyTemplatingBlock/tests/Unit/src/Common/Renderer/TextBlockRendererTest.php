<?php
declare(strict_types=1);

namespace EonX\EasyTemplatingBlock\Tests\Unit\Common\Renderer;

use EonX\EasyTemplatingBlock\Common\Renderer\TextBlockRenderer;
use EonX\EasyTemplatingBlock\Common\ValueObject\TemplateBlock;
use EonX\EasyTemplatingBlock\Common\ValueObject\TemplatingBlockInterface;
use EonX\EasyTemplatingBlock\Common\ValueObject\TextBlock;
use EonX\EasyTemplatingBlock\Tests\Unit\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class TextBlockRendererTest extends AbstractUnitTestCase
{
    /**
     * @see testRenderBlock
     */
    public static function provideRenderBlockData(): iterable
    {
        yield 'Simple text' => [
            (new TextBlock('my-text-block'))->setContents('my simple contents'),
            'my simple contents',
        ];
    }

    /**
     * @see testSupports
     */
    public static function provideSupportsData(): iterable
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

    #[DataProvider('provideRenderBlockData')]
    public function testRenderBlock(TextBlock $block, string $expected): void
    {
        $renderer = new TextBlockRenderer();

        self::assertEquals($expected, $renderer->renderBlock($block));
    }

    #[DataProvider('provideSupportsData')]
    public function testSupports(TemplatingBlockInterface $block, bool $expected): void
    {
        $renderer = new TextBlockRenderer();

        self::assertEquals($expected, $renderer->supports($block));
    }
}
