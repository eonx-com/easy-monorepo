<?php
declare(strict_types=1);

namespace App\Tests\Quality\Ecs\ArrangeActAssertSniff\Correct;

use App\Repositories\Interfaces\ProviderRuleRepositoryInterface;
use Mockery\MockInterface;

final class TestClass
{
    public function testSomething()
    {
        /** @var int $expectedResult */
        $expectedResult = 4;
        $ruleRepository = $this->mock(
            [ProviderRuleRepositoryInterface::class],
            static function (MockInterface $mock): void {
                $mock->shouldReceive('someMethod')
                    ->withNoArgs()
                    ->once()
                    ->andReturn([]);
            }
        );

        $actualResult = 2 + 2;

        self::assertSame($expectedResult, $actualResult);
        self::assertSame(['key' => 'value'], $array);
    }
}
