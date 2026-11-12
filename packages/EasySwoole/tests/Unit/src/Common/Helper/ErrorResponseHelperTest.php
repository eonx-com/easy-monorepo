<?php
declare(strict_types=1);

namespace EonX\EasySwoole\Tests\Unit\Common\Helper;

use EonX\EasySwoole\Common\Helper\ErrorResponseHelper;
use EonX\EasySwoole\Tests\Unit\AbstractUnitTestCase;
use RuntimeException;

final class ErrorResponseHelperTest extends AbstractUnitTestCase
{
    public function testItSucceeds(): void
    {
        $throwable = new RuntimeException('Missing env var DATABASE_URL');

        $result = ErrorResponseHelper::getContent($throwable);

        self::assertSame('{"error":"Internal Server Error"}', $result);
    }

    public function testItSucceedsInDebug(): void
    {
        $throwable = new RuntimeException('Missing env var DATABASE_URL');

        $result = ErrorResponseHelper::getContent($throwable, true);

        /** @var array{class: string, error: string, file: string, trace: list<array<string, int|string>>} $content */
        $content = \json_decode($result, true);
        self::assertSame('Missing env var DATABASE_URL', $content['error']);
        self::assertSame(RuntimeException::class, $content['class']);
        self::assertSame(__FILE__, $content['file']);
        self::assertNotEmpty($content['trace']);
    }
}
