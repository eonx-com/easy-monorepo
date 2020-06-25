<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\CorrectInheritDocAnnotation\Fixture;

use PHPUnit\Framework\TestCase;

class InheritDocSingleAnnotation extends TestCase
{
    /**
     * {@inheritDoc}
     */
    public function someMethod(): int
    {
        return 1;
    }
}

?>
-----
<?php
declare(strict_types=1);

namespace EonX\EasyStandard\Tests\Rector\CorrectInheritDocAnnotation\Fixture;

use PHPUnit\Framework\TestCase;

class InheritDocSingleAnnotation extends TestCase
{
    public function someMethod(): int
    {
        return 1;
    }
}

?>