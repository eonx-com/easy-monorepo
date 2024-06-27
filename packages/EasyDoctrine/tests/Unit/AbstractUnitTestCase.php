<?php
declare(strict_types=1);

namespace EonX\EasyDoctrine\Tests\Unit;

use Closure;
use EonX\EasyTest\Common\Trait\PrivatePropertyAccessTrait;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Filesystem\Filesystem;
use Throwable;

/**
 * This class has for objective to provide common features to all tests without having to update
 * the class they all extend.
 */
abstract class AbstractUnitTestCase extends TestCase
{
    use PrivatePropertyAccessTrait;
    use ProphecyTrait;

    protected ?Throwable $thrownException = null;

    protected function tearDown(): void
    {
        $fs = new Filesystem();
        $var = __DIR__ . '/../../var';

        if ($fs->exists($var)) {
            $fs->remove($var);
        }

        parent::tearDown();
    }

    /**
     * @throws \Exception
     */
    protected function assertThrownException(
        string $expectedException,
        int $code,
        ?string $previousException = null,
    ): void {
        self::assertNotNull($this->thrownException);

        if ($this->thrownException instanceof $expectedException === false) {
            throw $this->thrownException;
        }

        self::assertSame($code, $this->thrownException->getCode());

        if ($previousException === null) {
            self::assertNull($this->thrownException->getPrevious());
        }

        if ($previousException !== null) {
            self::assertTrue($this->thrownException->getPrevious() instanceof $previousException);
        }
    }

    protected function mock(mixed $target, ?callable $expectations = null): MockInterface
    {
        /** @var \Mockery\MockInterface $mock */
        $mock = Mockery::mock($target);

        if ($expectations !== null) {
            $expectations($mock);
        }

        return $mock;
    }

    protected function safeCall(Closure $func): void
    {
        try {
            $func();
        } catch (Throwable $exception) {
            $this->thrownException = $exception;
        }
    }
}
