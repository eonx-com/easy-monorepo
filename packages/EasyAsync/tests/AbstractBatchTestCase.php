<?php

declare(strict_types=1);

namespace EonX\EasyAsync\Tests;

abstract class AbstractBatchTestCase extends AbstractTestCase
{
    /**
     * @return iterable<mixed>
     */
    public function providerTestFromCallable(): iterable
    {
        yield 'simple' => [
            static function (): iterable {
                yield new \stdClass();
            },
            1,
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public function providerTestFromIterable(): iterable
    {
        yield 'array' => [
            [new \stdClass()],
            1,
        ];
    }
}
