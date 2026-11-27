<?php
declare(strict_types=1);

namespace EonX\EasyServerless\Tests\Unit\Uid\Hook;

use EonX\EasyServerless\Tests\Unit\AbstractUnitTestCase;
use EonX\EasyServerless\Uid\Hook\RegenerateUuidV6NodeHook;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use ReflectionProperty;
use Symfony\Component\Uid\UuidV6;

final class RegenerateUuidV6NodeHookTest extends AbstractUnitTestCase
{
    #[RunInSeparateProcess]
    public function testAfterRestoreDoesNothingWhenNodeIsNotInitialized(): void
    {
        $node = new ReflectionProperty(UuidV6::class, 'node');
        $sut = new RegenerateUuidV6NodeHook();

        $sut->afterRestore();

        self::assertFalse($node->isInitialized());
    }

    #[RunInSeparateProcess]
    public function testAfterRestoreRegeneratesInitializedNode(): void
    {
        new UuidV6();
        $node = new ReflectionProperty(UuidV6::class, 'node');
        /** @var string $previousNode */
        $previousNode = $node->getValue();
        $sut = new RegenerateUuidV6NodeHook();

        $sut->afterRestore();

        /** @var string $newNode */
        $newNode = $node->getValue();
        self::assertNotSame($previousNode, $newNode);
        self::assertMatchesRegularExpression('/^[0-9a-f]{12}$/', $newNode);
        self::assertSame(1, (int)\hexdec(\substr($newNode, 0, 2)) & 0x01);
    }
}
