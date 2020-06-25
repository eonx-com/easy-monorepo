<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\CorrectInheritDocAnnotation\Fixture;

use PHPUnit\Framework\TestCase;

class EmptyPhpDoc extends TestCase
{
    public function someMethod(): int
    {
        return 1;
    }
}