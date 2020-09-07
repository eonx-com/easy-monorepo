<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Correct;

final class TestClass
{
    public function testSomething()
    {
        self::assertSame(
            BaseExceptionInterface::DEFAULT_ERROR_CODE_RUNTIME,
            $this->createEventRequiredException()->getErrorCode()
        );
    }
}
